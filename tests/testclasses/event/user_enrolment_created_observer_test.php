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
use container_workspace\member\member;
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
 * @testdox When using a nudge with timing based on user enrolment
 * @coversDefaultClass \local_nudge\event\user_enrolment_created_observer
 */
class user_enrolment_created_observer_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @testdox a message is sent on any new enrolment
     * @covers ::trigger_user_enrolment_created_nudges
     */
    public function test_nudge_triggered_on_user_enrolment_created(): void
    {
        /** @var \core_config */
        global $CFG;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $sink = $this->redirectMessages();
        $generator = $this->getDataGenerator();

        $createuser = $generator->create_user();
        $recvuser = $generator->create_user();

        /** @var \container_workspace\testing\generator $workspace_generator */
        $workspace_generator = $generator->get_plugin_generator('container_workspace');

        // To set workspace creator
        $this->setUser($createuser);
        $workspace = $workspace_generator->create_workspace();

        $notification = nudge_notification_db::create_or_refresh(
            new nudge_notification(['userfromid' => $createuser->id])
        );
        $contents = nudge_notification_content_db::create_or_refresh(new nudge_notification_content([
            'nudgenotificationid' => $notification->id,
            'subject' => 'xyz',
            'body' => 'xyz'
        ]));
        $nudge = new nudge([
            'isenabled' => true,
            'courseid' => $workspace->get_id(),
            'linkedlearnernotificationid' => $notification->id,
            'remindertype' => nudge::REMINDER_DATE_USER_ENROLMENT,
        ]);

        nudge_db::save($nudge);

        $this->assertEquals(0, $sink->count());

        // Join via self enrolment
        $this->setUser($recvuser);
        member::join_workspace($workspace, $recvuser->id);

        $this->assertEquals(1, $sink->count());

        $this->assertIsArray($sink->get_messages());
        $message = $sink->get_messages()[0];
        $this->assertIsObject($message);

        $this->assertEquals('local_nudge', $message->component);
        $this->assertEquals($contents->subject, $message->subject);
        $this->assertEquals($contents->body, $message->fullmessage);
        $this->assertEquals($contents->body, $message->fullmessagehtml);
        $this->assertEquals($createuser->id, $message->useridfrom);
        $this->assertEquals($recvuser->id, $message->useridto);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}

