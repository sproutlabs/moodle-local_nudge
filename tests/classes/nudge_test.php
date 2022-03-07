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

namespace local_nudge\classes;

use advanced_testcase;
use local_nudge\dml\nudge_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;
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
        
        $this->resetAfterTest(true);

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

        $this->resetAfterTest(true);

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

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
