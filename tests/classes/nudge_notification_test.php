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
use local_nudge\dml\nudge_notification_content_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge_notification;
use local_nudge\local\nudge_notification_content;

// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine
// phpcs:disable moodle.Commenting.InlineComment.TypeHintingMatch

/**
 * @testdox When using a nudge_notification entity
 */
class nudge_notification_test extends advanced_testcase {

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @testdox Calling the get_content_for_lang function returns filtered has many instances of nudge_notification_content.
     * @covers local_nudge\local\nudge_notification::get_content_for_lang
     */
    public function test_get_content_for_lang(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        $this->resetAfterTest(true);

        $notification = new nudge_notification([]);
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

        $goteng = $notification->get_content_for_lang('en');

        // Should be defaults.
        $this->assertInstanceOf(nudge_notification_content::class, $goteng);
        $this->assertEquals($goteng->lang, nudge_notification_content::DEFAULTS['lang']);
        $this->assertEquals($goteng->subject, nudge_notification_content::DEFAULTS['subject']);
        $this->assertEquals($goteng->body, nudge_notification_content::DEFAULTS['body']);

        $gotbr = $notification->get_content_for_lang('br');

        $this->assertEquals($gotbr->lang, 'br');
        $this->assertEquals($gotbr->body, 'differentcontent');

        $gotgl = $notification->get_content_for_lang('gl');

        $this->assertNull($gotgl);

        $DB->delete_records(nudge_notification_db::$table);
        $DB->delete_records(nudge_notification_content_db::$table);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
