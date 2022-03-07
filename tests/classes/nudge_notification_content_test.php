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
 * @testdox When using a nudge_notification_content entity
 */
class nudge_notification_content_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @testdox Calling get_notification returns the has one in the expected state.
     * @small
     * @covers local_nudge\local\nudge_notification_content::get_notification
     */
    public function test_get_notification(): void
    {
        $this->resetAfterTest(true);

        /** @var \moodle_database $DB */
        global $DB;

        $notification = new nudge_notification([]);
        $notificationid = nudge_notification_db::save($notification);

        // Get with defaults and id.
        $notification = nudge_notification_db::get_by_id($notificationid);

        $content = new nudge_notification_content([
            'nudgenotificationid' => $notificationid
        ]);

        $resultingnotification = $content->get_notification();

        $this->assertEquals($notification, $resultingnotification);

        $DB->delete_records(nudge_notification_db::$table);
        $DB->delete_records(nudge_notification_content_db::$table);

    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
