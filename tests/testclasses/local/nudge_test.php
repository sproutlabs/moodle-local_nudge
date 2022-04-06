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

// phpcs:disable moodle.Commenting

/**
 * @package     local_nudge\tests
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\testclasses\local;

use advanced_testcase;
use local_nudge\dml\nudge_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;
use moodle_exception;
use stdClass;

// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine
// phpcs:disable moodle.Commenting.InlineComment.TypeHintingMatch

/**
 * @testdox When using a nudge entity
 */
class nudge_test extends advanced_testcase {

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @testdox Calling get_*_notification returns an instance of nudge_notification.
     * @covers local_nudge\local\nudge::get_learner_notification
     * @covers local_nudge\local\nudge::get_manager_notification
     */
    public function test_get_notification(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        $this->resetAfterTest();

        $courseid = $this->getDataGenerator()->create_course()->id;

        $learnernotification = new nudge_notification([]);
        $nudgenotificationid = nudge_notification_db::save($learnernotification);

        $nudge = new nudge([
            'courseid' => $courseid,
            'linkedlearnernotificationid' => $nudgenotificationid,
            'linkedmanagernotificationid' => $nudgenotificationid
        ]);

        $learnerresult = $nudge->get_learner_notification();
        $managerresult = $nudge->get_manager_notification();

        $this->assertInstanceOf(nudge_notification::class, $learnerresult);
        $this->assertInstanceOf(nudge_notification::class, $managerresult);

        $nullnudge = new nudge([]);

        $expectednull = $nullnudge->get_learner_notification();
        $this->assertNull($expectednull, 'A new nudge without a linked notification should not return anything');
        $expectednull = $nullnudge->get_manager_notification();

        // Cleanup.
        $DB->delete_records('course');
        $DB->delete_records(nudge_db::$table);
        $DB->delete_records(nudge_notification_db::$table);
    }

    /**
     * @test
     * @testdox Calling get_course on a nudge instance correctly returns a stdClass representing it's linked course.
     * @covers local_nudge\local\nudge::get_course
     */
    public function test_get_course(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();

        $nudge = new nudge([
            'courseid' => $course->id
        ]);

        $resultcourse = $nudge->get_course();

        $this->assertIsObject($resultcourse);
        $this->assertInstanceOf(stdClass::class, $resultcourse);
        $this->assertEquals($course->id, $resultcourse->id);
        $this->assertEquals($course->fullname, $resultcourse->fullname);

        // Cleanup.
        $DB->delete_records('course');
        $DB->delete_records(nudge_db::$table);
    }

    /**
     * @test
     * @testdox Triggering a nudge with invalid database (or runtime) data fails.
     * @covers local_nudge\local\nudge::trigger
     */
    public function test_trigger_expected(): void {
        $nudge = new nudge([]);
        $nudge->reminderrecipient = 'xyz';

        $this->expectException(moodle_exception::class);
        // Make sure its our moodle exception.
        $this->expectExceptionMessage(\get_string('expectedunreachable', 'local_nudge'));
        $nudge->trigger(new stdClass());
    }

    /**
     * @test
     * @testdox Creating a new instance will return sane correctly typed defaults.
     * @covers local_nudge\local\nudge::cast_fields
     */
    public function tests_defaults_casted() {
        /** @var \moodle_database $DB */
        global $DB;

        $this->resetAfterTest();

        $nudge = new nudge([
            'courseid' => 1
        ]);
        $nudgeid = nudge_db::save($nudge);
        $nudge = nudge_db::get_by_id($nudgeid);

        /**
         * Using two instead of assertSame() since its more verbose.
         * @var nudge $nudge
        */
        $this->assertIsInt($nudge->courseid);
        $this->assertEquals(1, $nudge->courseid);

        $this->assertIsInt($nudge->linkedlearnernotificationid);
        $this->assertEquals(0, $nudge->linkedlearnernotificationid);

        $this->assertIsInt($nudge->linkedmanagernotificationid);
        $this->assertEquals(0, $nudge->linkedmanagernotificationid);

        $this->assertIsString($nudge->title);
        $this->assertEquals('Untitled Nudge', $nudge->title);

        $this->assertIsBool($nudge->isenabled);
        $this->assertFalse($nudge->isenabled);

        $this->assertIsString($nudge->reminderrecipient);
        $this->assertEquals(nudge::REMINDER_RECIPIENT_LEARNER, $nudge->reminderrecipient);

        $this->assertIsString($nudge->remindertype);
        $this->assertEquals(nudge::REMINDER_DATE_RELATIVE_COURSE_END, $nudge->remindertype);

        $this->assertIsInt($nudge->remindertypefixeddate);
        $this->assertEquals(0, $nudge->remindertypefixeddate);

        $DB->delete_records(nudge_db::$table);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
