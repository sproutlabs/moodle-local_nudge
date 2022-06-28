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
use completion_completion;
use local_nudge\dml\nudge_db;
use local_nudge\dml\nudge_notification_content_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;
use local_nudge\local\nudge_notification_content;
use stdClass;

// phpcs:disable moodle.Commenting
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine

/**
 * @testdox When using a nudge with timing based on course completion
 * @coversDefaultClass \local_nudge\event\course_completed_observer
 */
class course_completion_observer_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @testdox a message is sent on course completion
     * @covers ::trigger_course_completion_nudges
     */
    public function test_nudge_triggered_on_course_completion(): void
    {
        /** @var \core_config */
        global $CFG;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $sink = $this->redirectMessages();

        /** @var \core\entity\course|stdClass */
        $course = $this->getDataGenerator()->create_course();

        /** @var \core\entity\user|stdClass */
        $user = $this->getDataGenerator()->create_and_enrol($course);

        $notification = nudge_notification_db::create_or_refresh(
            new nudge_notification(['userfromid' => $user->id])
        );
        $contents = nudge_notification_content_db::create_or_refresh(new nudge_notification_content([
            'nudgenotificationid' => $notification->id,
            'subject' => 'xyz',
            'body' => 'xyz'
        ]));
        $nudge = new nudge([
            'isenabled' => true,
            'courseid' => $course->id,
            'linkedlearnernotificationid' => $notification->id,
            'remindertype' => nudge::REMINDER_DATE_COURSE_COMPLETION,
        ]);

        nudge_db::save($nudge);

        $this->assertEquals(0, $sink->count());

        $coursecompletion = new completion_completion([
            'course' => $course->id,
            'userid' => $user->id,
        ]);
        $coursecompletion->mark_complete();

        // Check the correct ammount of emails were sent.
        if ($CFG->branch == '39') {
            // Only nudge.
            $expectedcount = 1;
        } else {
            // Nudge **and** the default moodle completion message introduced in 310.
            $expectedcount = 2;
        }
        $this->assertEquals($expectedcount, $sink->count());

        $this->assertIsArray($sink->get_messages());
        $message = $sink->get_messages()[0];
        $this->assertIsObject($message);

        $this->assertEquals('local_nudge', $message->component);
        $this->assertEquals($contents->subject, $message->subject);
        $this->assertEquals($contents->body, $message->fullmessage);
        $this->assertEquals($contents->body, $message->fullmessagehtml);
        $this->assertEquals($user->id, $message->useridfrom);
        $this->assertEquals($user->id, $message->useridto);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}

