<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package     local_nudge\tests
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\testclasses\event;

use advanced_testcase;
use context_course;
use local_nudge\dml\nudge_db;
use local_nudge\local\nudge;

use const CONTEXT_COURSE;

// phpcs:disable moodle.Commenting
// phpcs:disable Squiz.PHP.CommentedOutCode
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine

/**
 * These tests are much slower the rest because of {@see self::preventResetByRollback}. Needed to test events.
 * @testdox When preforming CRUD operations on nudge
 */
class nudge_event_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();

        $this->preventResetByRollback();
        $this->resetAfterTest();

        set_config('enabled_stores', 'logstore_standard', 'tool_log');
        set_config('buffersize', 0, 'logstore_standard');
        get_log_manager(true);
    }

    /**
     * @test
     * @large
     * @testdox Saving should create a new nudge_created event.
     * @covers local_nudge\event\nudge_created
     */
    public function test_nudge_created_event(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        /** @var \core\entity\user|stdClass */
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        /** @var \core\entity\course|stdClass */
        $course = $this->getDataGenerator()->create_course();

        $nudge = new nudge([
            'courseid' => $course->id
        ]);
        nudge_db::save($nudge);

        $expectedfilter = [
            'eventname' => '\\local_nudge\\event\\nudge_created',
            'component' => 'local_nudge',
            'action' => 'created',
            'target' => 'nudge',
            'objecttable' => nudge_db::$table,
            'crud' => 'c',
            'contextid' => context_course::instance($course->id)->id,
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $user->id,
            'courseid' => $course->id,
        ];

        $eventexists = $DB->record_exists('logstore_standard_log', $expectedfilter);

        $this->assertTrue($eventexists);
    }

    /**
     * @test
     * @large
     * @testdox Deleting should create a new nudge_deleted event.
     * @covers local_nudge\event\nudge_deleted
     */
    public function test_nudge_deleted_event(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        /** @var \core\entity\user|stdClass */
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        /** @var \core\entity\course|stdClass */
        $course = $this->getDataGenerator()->create_course();

        $nudge = new nudge([
            'courseid' => $course->id
        ]);
        $nudgeid = nudge_db::save($nudge);

        $createdfilter = [
            'eventname' => '\\local_nudge\\event\\nudge_created',
            'component' => 'local_nudge',
            'action' => 'created',
            'target' => 'nudge',
            'objecttable' => nudge_db::$table,
            'crud' => 'c',
            'contextid' => context_course::instance($course->id)->id,
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $user->id,
            'courseid' => $course->id,
        ];

        $createdeventexists = $DB->record_exists('logstore_standard_log', $createdfilter);

        $this->assertTrue($createdeventexists);

        nudge_db::delete($nudgeid);

        $deletedfilter = [
            'eventname' => '\\local_nudge\\event\\nudge_deleted',
            'component' => 'local_nudge',
            'action' => 'deleted',
            'target' => 'nudge',
            'objecttable' => nudge_db::$table,
            'crud' => 'd',
            'contextid' => context_course::instance($course->id)->id,
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $user->id,
            'courseid' => $course->id,
        ];

        $deletedeventexists = $DB->record_exists('logstore_standard_log', $deletedfilter);

        $this->assertTrue($deletedeventexists);
    }

    /**
     * @test
     * @large
     * @testdox Saving an existing instance should create a new nudge_updated event.
     * @covers local_nudge\event\nudge_updated
     */
    public function test_nudge_updated_event(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        /** @var \core\entity\user|stdClass */
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        /** @var \core\entity\course|stdClass */
        $course = $this->getDataGenerator()->create_course();

        $nudge = new nudge([
            'courseid' => $course->id
        ]);
        $nudgeid = nudge_db::save($nudge);
        $nudge = nudge_db::get_by_id($nudgeid);

        $createdfilter = [
            'eventname' => '\\local_nudge\\event\\nudge_created',
            'component' => 'local_nudge',
            'action' => 'created',
            'target' => 'nudge',
            'objecttable' => nudge_db::$table,
            'crud' => 'c',
            'contextid' => context_course::instance($course->id)->id,
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $user->id,
            'courseid' => $course->id,
        ];

        $createdeventexists = $DB->record_exists('logstore_standard_log', $createdfilter);

        $this->assertTrue($createdeventexists);

        $nudge->isenabled = 1;
        nudge_db::save($nudge);

        $updatedfilter = [
            'eventname' => '\\local_nudge\\event\\nudge_updated',
            'component' => 'local_nudge',
            'action' => 'updated',
            'target' => 'nudge',
            'objecttable' => nudge_db::$table,
            'crud' => 'u',
            'contextid' => context_course::instance($course->id)->id,
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $user->id,
            'courseid' => $course->id,
        ];

        $updatedeventexists = $DB->record_exists('logstore_standard_log', $updatedfilter);

        $this->assertTrue($updatedeventexists);
    }

    public function tearDown(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        $DB->delete_records(nudge_db::$table);
        $DB->delete_records('course');
        $DB->delete_records('user');

        // Clear the user.
        $this->setUser(null);

        parent::tearDown();
    }
}
