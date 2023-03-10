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
 * @package     local_nudge\testclasses\form\nudge
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\testclasses\form\nudge;

use advanced_testcase;
use local_nudge\dml\nudge_notification_db;
use local_nudge\form\nudge\edit;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;

// phpcs:disable moodle.Files.LineLength
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine

/**
 * @coversDefaultClass \local_nudge\form\nudge\edit
 * @testdox When working with a nudge form
 *
 * An aside when working with HTMLQuickform via $_POST mocking, Sometimes it will be a giant PIA to debug.
 * One of the key things to remember is that it takes a dynamic moodleform definition into account.
 *
 * So if you $_POST a value for a select that gets populated from the database it may silently omit
 * that value if there isn't any data avalible.
 */
class edit_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();

        $this->resetAfterTest();
        $this->mock_nudge_edit_form_post();
    }

    /**
     * Covers since there is only one rule in definition at the time of testing.
     *
     * @test
     * @testdox attempting to save a {@see nudge} without a title will not work.
     * @covers ::definition
     * @covers ::get_data
     */
    public function test_nudge_edit_submit_required_title(): void
    {
        $notification = nudge_notification_db::create_or_refresh(new nudge_notification());
        $_POST['linkedlearnernotificationid'] = $notification->id;
        $this->assertNotNull($this->get_form_data(), 'Setting up the learner notification should be valid.');

        unset($_POST['group_header']['title']);
        $this->assertNull($this->get_form_data());
    }

    /**
     * @test
     * @testdox Submitting a form with {@see nudge::REMINDER_RECIPIENT_BOTH} requires two selected {@see nudge_notification}s.
     * @covers ::validation
     * @covers ::get_data
     */
    public function test_nudge_edit_submit_recipient_both(): void
    {
        $_POST['reminderrecipient'] = nudge::REMINDER_RECIPIENT_BOTH;
        $notification = nudge_notification_db::create_or_refresh(new nudge_notification());

        $this->assertStringContainsString(
            'The selected recipient type was: "Both the Learner and their Managers" but there wasn\'t wasn\'t enough notifications to cover the recipients.',
            $this->get_form_display()
        );

        $_POST['linkedlearnernotificationid'] = $notification->id;
        $this->assertNull($this->get_form_data(), 'Still needs another notification');

        $_POST['linkedmanagernotificationid'] = $notification->id;
        $data = $this->get_form_data();
        $this->assertNotNull($data);
        $this->assertInstanceOf(nudge::class, $data);
        $this->assertEquals($notification->id, $data->linkedlearnernotificationid);
        $this->assertEquals($notification->id, $data->linkedmanagernotificationid);
    }

    /**
     * @test
     * @testdox Submitting a form with {@see nudge::REMINDER_RECIPIENT_LEARNER} requires one selected {@see nudge_notification}.
     * @covers ::validation
     * @covers ::get_data
     */
    public function test_nudge_edit_submit_recipient_learner(): void
    {
        $_POST['reminderrecipient'] = nudge::REMINDER_RECIPIENT_LEARNER;
        $notification = nudge_notification_db::create_or_refresh(new nudge_notification());

        $this->assertStringContainsString(
            'The selected recipient type was: "The Learner" but there wasn\'t wasn\'t enough notifications to cover the recipients.',
            $this->get_form_display()
        );

        $_POST['linkedlearnernotificationid'] = $notification->id;

        $data = $this->get_form_data();
        $this->assertNotNull($data);
        $this->assertInstanceOf(nudge::class, $data);
        $this->assertEquals($notification->id, $data->linkedlearnernotificationid);
        $this->assertEquals(0, $data->linkedmanagernotificationid);
    }

    /**
     * @test
     * @testdox Submitting a form with {@see nudge::REMINDER_RECIPIENT_MANAGERS} requires one selected {@see nudge_notification}.
     * @covers ::validation
     * @covers ::get_data
     */
    public function test_nudge_edit_submit_recipient_manager(): void
    {
        $_POST['reminderrecipient'] = nudge::REMINDER_RECIPIENT_MANAGERS;
        $notification = nudge_notification_db::create_or_refresh(new nudge_notification());

        $this->assertStringContainsString(
            'The selected recipient type was: "The Learner\'s Managers" but there wasn\'t wasn\'t enough notifications to cover the recipients.',
            $this->get_form_display()
        );

        $_POST['linkedmanagernotificationid'] = $notification->id;

        $data = $this->get_form_data();
        $this->assertNotNull($data);
        $this->assertInstanceOf(nudge::class, $data);
        $this->assertEquals($notification->id, $data->linkedmanagernotificationid);
        $this->assertEquals(0, $data->linkedlearnernotificationid);
    }

    /**
     * @test
     * @testdox Submitting a form with {@see nudge::REMINDER_DATE_INPUT_FIXED} validates the date against the courses end date.
     * @covers ::validation
     * @covers ::get_data
     */
    public function test_nudge_edit_submit_fixed_validate_courseend(): void
    {
        $notification = nudge_notification_db::create_or_refresh(new nudge_notification());
        $_POST['linkedlearnernotificationid'] = $notification->id;

        $initaltimestamp = \time();

        $course = $this->getDataGenerator()->create_course(['enddate' => $initaltimestamp]);
        $_POST['courseid'] = $course->id;

        $_POST['remindertypefixeddate']['day'] = \date('j', $initaltimestamp);
        $_POST['remindertypefixeddate']['month'] = \date('n', $initaltimestamp);
        $_POST['remindertypefixeddate']['year'] = \date('Y', $initaltimestamp + \YEARSECS);

        $this->assertStringContainsString(
            \get_string(
                'validation_nudge_timepastcourseend',
                'local_nudge',
                \date(nudge::DATE_FORMAT_NICE, $initaltimestamp)
            ),
            $this->get_form_display()
        );
    }

    /**
     * Gets the submited result of a {@see nudge} {@see edit} form.
     *
     * @return nudge|null
     */
    private function get_form_data(): ?nudge
    {
        $edit = new edit();
        $this->assertFalse($edit->is_cancelled());
        return $edit->get_data();
    }

    /**
     * Gets a {@see nudge} {@see edit} form's HTML output.
     *
     * This is for validation its not ideal to scan the string but I can't find the right API right now.
     *
     * @return string
     */
    private function get_form_display(): string
    {
        $edit = new edit();
        $this->assertFalse($edit->is_cancelled());
        $edit->get_data();

        \ob_start();
        $edit->display();
        return \ob_get_clean() ?: '';
    }

    /**
     * Mocks a simple post to {@see nudge}'s {@see edit} form.
     *
     * @return void
     */
    private function mock_nudge_edit_form_post(): void
    {
        $course = $this->getDataGenerator()->create_course([
            'enddate' => (\time() + (\YEARSECS * 10))
        ]);
        $_POST = [
            'id' => '0',
            'courseid' => (string) $course->id,
            'sesskey' => \sesskey(),
            '_qf__local_nudge_form_nudge_edit' => '1',
            'group_header' => [
                'title' => 'testing',
                'isenabled' => '1'
            ],
            'reminderrecipient' => nudge::REMINDER_RECIPIENT_LEARNER,
            'remindertype' => nudge::REMINDER_DATE_INPUT_FIXED,
            'remindertypefixeddate' => [
                'day' => '6',
                'month' => '4',
                'year' => '2022',
            ],
            'submitbutton' => 'Save changes',
        ];
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
