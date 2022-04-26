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
// phpcs:disable Squiz.PHP.CommentedOutCode
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine

/**
 * @package     local_nudge\tests\dml
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\testclasses\dml;

use advanced_testcase;
use local_nudge\dml\nudge_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;

/**
 * @coversDefaultClass \local_nudge\dml\nudge_notification_db
 * @testdox Whilst subclassing and using a abstract nudge database modification layer
 */
class nudge_notification_db_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();

        $this->resetAfterTest();
    }

    /**
     * @test
     * @testdox deleting a nudge notification will unlink it from any attached nudges.
     * @covers ::delete
     */
    public function test_delete_unsets_attached_nudges(): void
    {
        $this->redirectMessages();

        $notification = nudge_notification_db::create_or_refresh(new nudge_notification());

        $course = $this->getDataGenerator()->create_course();
        $nudge = new nudge();
        $nudge->courseid = $course->id;
        $nudge->linkedlearnernotificationid = $notification->id;
        $nudge->linkedmanagernotificationid = $notification->id;
        $nudge->isenabled = true;
        $nudgeid = nudge_db::save($nudge);

        $this->assertCount(
            1,
            nudge_db::get_all_filtered([
                'linkedlearnernotificationid' => $notification->id,
                'linkedmanagernotificationid' => $notification->id,
                'isenabled' => true
            ])
        );

        nudge_notification_db::delete($notification->id);

        $modifiednudge = nudge_db::get_by_id($nudgeid);
        $this->assertFalse($modifiednudge->isenabled);
        $this->assertEquals(0, $modifiednudge->linkedlearnernotificationid);
        $this->assertEquals(0, $modifiednudge->linkedmanagernotificationid);
    }


    /**
     * @test
     * @testdox when deleting a nudge notification if there are removed nudge links emails are sent to the nudge's owner.
     * @covers ::send_deletion_message
     * @covers \local_nudge\local\nudge::notify_owners
     */
    public function test_delete_emails_owners(): void
    {
        $sink = $this->redirectMessages();

        $notification = nudge_notification_db::create_or_refresh(new nudge_notification());

        $creatinguser = $this->getDataGenerator()->create_user();
        $updatinguser = $this->getDataGenerator()->create_user();

        $course = $this->getDataGenerator()->create_course();
        $nudge = new nudge();
        $nudge->courseid = $course->id;
        $nudge->linkedlearnernotificationid = $notification->id;
        $nudge->linkedmanagernotificationid = $notification->id;
        $nudge->isenabled = true;

        $this->setUser($creatinguser);
        $nudge = nudge_db::create_or_refresh($nudge);

        $this->setUser($updatinguser);
        $nudge = nudge_db::create_or_refresh($nudge);

        nudge_notification_db::delete($notification->id);

        $this->assertEquals(4, $sink->count());

        $messagesgot = $sink->get_messages();
        $receipts = \array_column($messagesgot, 'useridto');
        $this->assertContains($creatinguser->id, $receipts);
        $this->assertContains($updatinguser->id, $receipts);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
