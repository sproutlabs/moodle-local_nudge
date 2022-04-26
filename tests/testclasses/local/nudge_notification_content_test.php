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
use local_nudge\dml\nudge_notification_content_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge_notification;
use local_nudge\local\nudge_notification_content;

// phpcs:disable moodle.Commenting
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine

/**
 * @coversDefaultClass \local_nudge\local\nudge_notification_content
 * @testdox When using a nudge_notification_content entity
 */
class nudge_notification_content_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();

        $this->resetAfterTest();
    }

    /**
     * @test
     * @testdox Calling get_notification returns the has one in the expected state.
     * @covers ::get_notification
     */
    public function test_get_notification(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        $unlinkedcontent = new nudge_notification_content();
        $this->assertNull($unlinkedcontent->get_notification());

        $notification = new nudge_notification();
        $notificationid = nudge_notification_db::save($notification);

        // Get with defaults and id.
        $notification = nudge_notification_db::get_by_id($notificationid);

        $content = new nudge_notification_content([
            'nudgenotificationid' => $notificationid
        ]);

        $resultingnotification = $content->get_notification();

        $this->assertEquals($notification, $resultingnotification);
    }


    /**
     * @test
     * @testdox Creating a new instance will return sane correctly typed defaults.
     * @covers ::cast_fields
     */
    public function test_defaults_casted() {
        /** @var nudge_notification_content */
        $contents = nudge_notification_content_db::create_or_refresh(
            new nudge_notification_content()
        );

        $this->assertIsInt($contents->nudgenotificationid);
        $this->assertEquals(0, $contents->nudgenotificationid);

        $this->assertIsString($contents->lang);
        $this->assertEquals(nudge_notification_content::DEFAULTS['lang'], $contents->lang);

        $this->assertIsString($contents->subject);
        $this->assertEquals(nudge_notification_content::DEFAULTS['subject'], $contents->subject);

        $this->assertIsString($contents->body);
        $this->assertEquals(nudge_notification_content::DEFAULTS['body'], $contents->body);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
