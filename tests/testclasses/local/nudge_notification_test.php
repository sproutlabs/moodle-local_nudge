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

namespace local_nudge\testclasses\local;

use advanced_testcase;
use core_user;
use local_nudge\dml\nudge_notification_content_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\dto\nudge_notification_form_data;
use local_nudge\local\nudge_notification;
use local_nudge\local\nudge_notification_content;
use moodle_exception;
use Throwable;

// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine
// phpcs:disable moodle.Commenting.InlineComment.TypeHintingMatch

/**
 * @coversDefaultClass \local_nudge\local\nudge_notification
 * @testdox When using a nudge_notification entity
 */
class nudge_notification_test extends advanced_testcase {

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @testdox Calling the get_contents function returns filtered has many instances of nudge_notification_content.
     * @covers ::get_contents
     */
    public function test_get_contents(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        $this->resetAfterTest();

        $notification = new nudge_notification();
        $notificationid = nudge_notification_db::save($notification);

        // Refresh the model with it's ID. Save clones the entity and that worth keeping in mind while developing.
        $notification = nudge_notification_db::get_by_id($notificationid);

        $eng = new nudge_notification_content([
            'nudgenotificationid' => $notificationid,
        ]);
        nudge_notification_content_db::save($eng);

        $br = new nudge_notification_content([
            'nudgenotificationid' => $notificationid,
            'lang' => 'br',
            'body' => 'differentcontent'
        ]);
        nudge_notification_content_db::save($br);

        $goteng = $notification->get_contents('en');

        $this->assertCount(1, $goteng, 'There should only be one en translation returned');
        $goteng = $goteng[0];

        // Should be defaults.
        $this->assertInstanceOf(nudge_notification_content::class, $goteng);
        $this->assertEquals($goteng->lang, nudge_notification_content::DEFAULTS['lang']);
        $this->assertEquals($goteng->subject, nudge_notification_content::DEFAULTS['subject']);
        $this->assertEquals($goteng->body, nudge_notification_content::DEFAULTS['body']);

        $gotbr = $notification->get_contents('br')[0];

        $this->assertEquals($gotbr->lang, 'br');
        $this->assertEquals($gotbr->body, 'differentcontent');

        $gotgl = $notification->get_contents('gl');

        $this->assertCount(0, $gotgl);

        $enandbr = $notification->get_contents();

        $this->assertCount(2, $enandbr, 'get_contents without arguments should return all records');

        $DB->delete_records(nudge_notification_db::$table);
        $DB->delete_records(nudge_notification_content_db::$table);
    }

    /**
     * @test
     * @testdox Wrapping a {@see nudge_notification} in a {@see nudge_notification_form_data} stores the correct data.
     * @covers ::as_notification_form
     * @covers \local_nudge\dto\nudge_notification_form_data
     */
    public function test_as_notification_form(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        $this->resetAfterTest();

        $notification = new nudge_notification([
            'title' => 'example',
        ]);
        $notificationid = nudge_notification_db::save($notification);
        $notification->id = $notificationid;

        $data = [
            'en' => [
                'nudgenotificationid' => $notificationid,
                'lang' => 'en',
                'body' => 'content'
            ],
            'br' => [
                'nudgenotificationid' => $notificationid,
                'lang' => 'br',
                'body' => 'differentcontent'
            ]
        ];

        foreach ($data as $lang => $data) {
            $lang = new nudge_notification_content($data);
            nudge_notification_content_db::save($lang);
        }

        $result = $notification->as_notification_form();

        $this->assertInstanceOf(
            nudge_notification_form_data::class,
            $result,
            'as_notification_form should return a notification form data dto'
        );
        $this->assertInstanceOf(nudge_notification::class, $result->notification);
        $this->assertEquals('example', $result->notification->title);
        $langs = \array_column($result->notificationcontents, 'lang');
        $this->assertContains('en', $langs);
        $this->assertContains('br', $langs);

        $DB->delete_records(nudge_notification_db::$table);
        $DB->delete_records(nudge_notification_content_db::$table);
    }

    /**
     * @test
     * @testdox getting a content for a user has sensible defaults when their language is not supported.
     * @covers ::get_users_contents
     */
    public function test_get_users_contents() {
        $this->resetAfterTest();

        // Ignoring the mtrace warning about the failure to resolve language -> contents.
        // phpcs:ignore
        $this->setOutputCallback(function () {});

        // Default lang is 'en'.
        $user = $this->getDataGenerator()->create_user();

        /** @var nudge_notification $notification */
        $notification = nudge_notification_db::create_or_refresh(new nudge_notification(['title' => 'testing']));

        // If a notification has no contents some form validation fails.
        // Here we should fail fast and let the cron worker know with an exception.
        try {
            $result = $notification->get_users_contents($user);
        // phpcs:ignore
        } catch (Throwable $t) {
        }
        $this->assertInstanceOf(moodle_exception::class, $t);
        $this->assertSame(get_string('expectedunreachable', 'local_nudge'), $t->getMessage());
        $this->assertFalse(isset($result));

        // Now we create two sets of content but neither for the user's language.
        // The expected behaviour here is that `get_users_contents` returns the first (primary key)
        // item.
        $expectedcontents = nudge_notification_content_db::create_or_refresh(new nudge_notification_content([
            'nudgenotificationid' => $notification->id,
            'lang' => 'br',
            'body' => 'languagebr'
        ]));
        nudge_notification_content_db::save(new nudge_notification_content([
            'nudgenotificationid' => $notification->id,
            'lang' => 'en_us',
            'body' => 'languageen_us'
        ]));
        $resultcontents = $notification->get_users_contents($user);
        $this->assertStringContainsString('our language is not supported', $resultcontents->body);

        // Apart from body they should be the same.
        unset($expectedcontents->body);
        unset($resultcontents->body);
        $this->assertEquals($expectedcontents, $resultcontents);

        // Finally we create content for the user's actual language and despite it's higher primary key it should be choosen.
        $userscontents = nudge_notification_content_db::create_or_refresh(new nudge_notification_content([
            'nudgenotificationid' => $notification->id,
            'lang' => 'en',
            'body' => 'works!'
        ]));

        $resultuserscontents = $notification->get_users_contents($user);
        $this->assertEquals($userscontents, $resultuserscontents);
        $this->assertSame($userscontents->body, $resultuserscontents->body);
    }

    /**
     * @test
     * @testdox Creating a new instance will return sane correctly typed defaults.
     * @covers ::cast_fields
     * @covers \local_nudge\dml\nudge_notification_db::on_before_create
     */
    public function test_defaults_casted() {
        /** @var \moodle_database $DB */
        global $DB;

        $this->resetAfterTest();

        $notification = new nudge_notification();
        $notificationid = nudge_notification_db::save($notification);
        $notification = nudge_notification_db::get_by_id($notificationid);

        $this->assertIsString($notification->title);
        $this->assertSame('Untitled Notification', $notification->title);
        $this->assertIsInt($notification->userfromid);
        $this->assertSame(core_user::get_noreply_user()->id, $notification->userfromid);

        $DB->delete_records(nudge_notification_db::$table);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
