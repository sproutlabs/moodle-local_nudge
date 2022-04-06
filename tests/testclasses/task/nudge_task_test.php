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

namespace local_nudge\testclasses\task;

use advanced_testcase;
use local_nudge\dml\nudge_db;
use local_nudge\dml\nudge_notification_content_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;
use local_nudge\local\nudge_notification_content;
use local_nudge\task\nudge_task;

// phpcs:disable moodle.Commenting
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine

/**
 * @testdox When the nudge_tasks runs it should
 */
class nudge_task_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Send emails to all users for a {@see nudge} with a {@see nudge::REMINDER_DATE_INPUT_FIXED}
     */
    public function test_send_fixed_date(): void
    {
        // -------------------------------
        //          SETUP TEST
        // -------------------------------
        /** @var \core_config $CFG */
        global $CFG;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $sink = $this->redirectMessages();

        // -------------------------------
        //          SETUP DATA
        // -------------------------------

        $time = \time();
        $CFG->nudgemocktime = $time;

        $course = $this->getDataGenerator()->create_course();
        $sender = $this->getDataGenerator()->create_user();
        $user = $this->getDataGenerator()->create_and_enrol($course);

        // TODO: dataGenerators for nudge, notification and contents.
        $notification = nudge_notification_db::create_or_refresh(
            new nudge_notification(['userfromid' => $sender->id])
        );
        $contents = nudge_notification_content_db::create_or_refresh(new nudge_notification_content([
            'nudgenotificationid' => $notification->id
        ]));
        nudge_db::create_or_refresh(new nudge([
            'isenabled' => 1,
            'courseid' => $course->id,
            'linkedlearnernotificationid' => $notification->id,
            'remindertype' => nudge::REMINDER_DATE_INPUT_FIXED,
            // Still in the future.
            'remindertypefixeddate' => $time + 1
        ]));

        $courselink = $CFG->wwwroot . "/course/view.php?id=" . $course->id;

        $vars = [
            '{user_firstname}' => $user->firstname,
            '{user_lastname}' => $user->lastname,
            '{course_fullname}' => $course->fullname,
            '{course_link}' => $courselink,
            '{sender_email}' => $sender->email,
        ];
        $expectedbody = \strtr(nudge_notification_content::DEFAULTS['body'], $vars);

        // -------------------------------
        //      PERFORM ASSERTIONS
        // -------------------------------

        (new nudge_task)->execute();

        $this->assertEquals(0, $sink->count(), 'The message should not go out yet.');

        $CFG->nudgemocktime += 2;

        (new nudge_task)->execute();

        $this->assertEquals(1, $sink->count(), 'The message was not sent when expected.');

        $message = $sink->get_messages()[0];

        $this->assertEquals($sender->id,            $message->useridfrom);
        $this->assertEquals($user->id,              $message->useridto);
        $this->assertEquals($contents->subject,     $message->subject);
        $this->assertNull($message->fullmessage);
        $this->assertEquals(\FORMAT_HTML,           $message->fullmessageformat);
        $this->assertEquals($expectedbody,          $message->fullmessagehtml);
        $this->assertNull($message->smallmessage);
        $this->assertEquals('local_nudge',          $message->component);
        $this->assertEquals('learneremail',         $message->eventtype);
        $this->assertEquals($courselink,            $message->contexturl);
        $this->assertEquals('Course Link',          $message->contexturlname);
        $this->assertNull($message->timeread);
        $this->assertNull($message->customdata);
        $this->assertEquals(1,                      $message->notification);
    }

    /**
     * Send emails to all users for a {@see nudge} with a {@see nudge::REMINDER_DATE_RELATIVE_COURSE_END}
     */
    public function test_send_relative_courseend(): void
    {
        // -------------------------------
        //          SETUP TEST
        // -------------------------------
        /** @var \core_config $CFG */
        global $CFG;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $sink = $this->redirectMessages();

        // -------------------------------
        //          SETUP DATA
        // -------------------------------

        $time = \time();
        $CFG->nudgemocktime = $time;

        $course = $this->getDataGenerator()->create_course([
            'enddate' => $time + 10
        ]);
        $sender = $this->getDataGenerator()->create_user();
        $user = $this->getDataGenerator()->create_and_enrol($course);

        // TODO: dataGenerators for nudge, notification and contents.
        $notification = nudge_notification_db::create_or_refresh(
            new nudge_notification(['userfromid' => $sender->id])
        );
        $contents = nudge_notification_content_db::create_or_refresh(new nudge_notification_content([
            'nudgenotificationid' => $notification->id
        ]));
        $nudge = nudge_db::create_or_refresh(new nudge([
            'isenabled' => 1,
            'courseid' => $course->id,
            'linkedlearnernotificationid' => $notification->id,
            'remindertype' => nudge::REMINDER_DATE_RELATIVE_COURSE_END,
            // Reminder 5 seconds before the course ends which is in 10 seconds from now.
            'remindertypeperiod' => 5
        ]));

        $courselink = $CFG->wwwroot . "/course/view.php?id=" . $course->id;

        $vars = [
            '{user_firstname}' => $user->firstname,
            '{user_lastname}' => $user->lastname,
            '{course_fullname}' => $course->fullname,
            '{course_link}' => $courselink,
            '{sender_email}' => $sender->email,
        ];
        $expectedbody = \strtr(nudge_notification_content::DEFAULTS['body'], $vars);

        // -------------------------------
        //      PERFORM ASSERTIONS
        // -------------------------------

        (new nudge_task)->execute();

        $this->assertEquals(0, $sink->count(), 'The message should not go out yet.');

        $CFG->nudgemocktime += 5;

        (new nudge_task)->execute();

        $this->assertEquals(1, $sink->count(), 'The message was not sent when expected.');

        $this->assertFalse(nudge_db::get_by_id($nudge->id)->isenabled, 'The instance should now be disabled.');

        $message = $sink->get_messages()[0];

        $this->assertEquals($sender->id,            $message->useridfrom);
        $this->assertEquals($user->id,              $message->useridto);
        $this->assertEquals($contents->subject,     $message->subject);
        $this->assertNull($message->fullmessage);
        $this->assertEquals(\FORMAT_HTML,           $message->fullmessageformat);
        $this->assertEquals($expectedbody,          $message->fullmessagehtml);
        $this->assertNull($message->smallmessage);
        $this->assertEquals('local_nudge',          $message->component);
        $this->assertEquals('learneremail',         $message->eventtype);
        $this->assertEquals($courselink,            $message->contexturl);
        $this->assertEquals('Course Link',          $message->contexturlname);
        $this->assertNull($message->timeread);
        $this->assertNull($message->customdata);
        $this->assertEquals(1,                      $message->notification);
    }

    /**
     * Send emails to all users for a {@see nudge} with a {@see nudge::REMINDER_DATE_RELATIVE_ENROLLMENT}
     */
    public function test_send_relative_enrollment(): void
    {
        // -------------------------------
        //          SETUP TEST
        // -------------------------------
        /** @var \core_config $CFG */
        global $CFG;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $sink = $this->redirectMessages();

        // -------------------------------
        //          SETUP DATA
        // -------------------------------

        $time = \time();
        $CFG->nudgemocktime = $time;

        $course = $this->getDataGenerator()->create_course([]);
        $sender = $this->getDataGenerator()->create_user();
        $user = $this->getDataGenerator()->create_and_enrol($course);

        // TODO: dataGenerators for nudge, notification and contents.
        $notification = nudge_notification_db::create_or_refresh(
            new nudge_notification(['userfromid' => $sender->id])
        );
        $contents = nudge_notification_content_db::create_or_refresh(new nudge_notification_content([
            'nudgenotificationid' => $notification->id
        ]));
        nudge_db::create_or_refresh(new nudge([
            'isenabled' => 1,
            'courseid' => $course->id,
            'linkedlearnernotificationid' => $notification->id,
            'remindertype' => nudge::REMINDER_DATE_RELATIVE_ENROLLMENT,
            // Every 2 minutes after the user's enrolment. Note that the user recieves a message on the first run.
            'remindertypeperiod' => \MINSECS * 2
        ]));

        $courselink = $CFG->wwwroot . "/course/view.php?id=" . $course->id;

        $vars = [
            '{user_firstname}' => $user->firstname,
            '{user_lastname}' => $user->lastname,
            '{course_fullname}' => $course->fullname,
            '{course_link}' => $courselink,
            '{sender_email}' => $sender->email,
        ];
        $expectedbody = \strtr(nudge_notification_content::DEFAULTS['body'], $vars);

        // -------------------------------
        //      PERFORM ASSERTIONS
        // -------------------------------

        (new nudge_task)->execute();

        $this->assertEquals(1, $sink->count(), 'There should be an immediate message to the user.');

        $CFG->nudgemocktime += (\MINSECS * 2) - 1;

        (new nudge_task)->execute();

        $this->assertEquals(1, $sink->count(), 'The message was sent early.');

        $CFG->nudgemocktime += 1;

        (new nudge_task)->execute();

        $this->assertEquals(2, $sink->count(), 'The user should now have a second message.');

        // We don't care for the internal message id.
        foreach ($sink->get_messages() as &$message) {
            unset($message->id);
        }

        $this->assertEquals($sink->get_messages()[0], $sink->get_messages()[1], 'The messages should be the same.');

        $message = $sink->get_messages()[0];

        $this->assertEquals($sender->id,            $message->useridfrom);
        $this->assertEquals($user->id,              $message->useridto);
        $this->assertEquals($contents->subject,     $message->subject);
        $this->assertNull($message->fullmessage);
        $this->assertEquals(\FORMAT_HTML,           $message->fullmessageformat);
        $this->assertEquals($expectedbody,          $message->fullmessagehtml);
        $this->assertNull($message->smallmessage);
        $this->assertEquals('local_nudge',          $message->component);
        $this->assertEquals('learneremail',         $message->eventtype);
        $this->assertEquals($courselink,            $message->contexturl);
        $this->assertEquals('Course Link',          $message->contexturlname);
        $this->assertNull($message->timeread);
        $this->assertNull($message->customdata);
        $this->assertEquals(1,                      $message->notification);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
