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
 * @package     local_nudge\testclasses\form\nudge_notification
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\testclasses\form\nudge_notification;

use advanced_testcase;
use local_nudge\dto\nudge_notification_form_data;
use local_nudge\form\nudge_notification\edit;
use local_nudge\local\nudge_notification;
use local_nudge\local\nudge_notification_content;

// phpcs:disable moodle.Commenting
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine

/**
 * @coversDefaultClass \local_nudge\form\nudge_notification\edit
 * @testdox When working with a nudge notifification form
 */
class edit_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();

        $this->resetAfterTest();
        $this->mock_nudge_notification_edit_form_post();

        // Hacky, the html editor element checks for a page url.
        global $PAGE;
        /** @var \moodle_page $PAGE */
        $PAGE->set_url('/xyz/testing');
    }

    /**
     * @test
     * @testdox a simple submission passes validation and returns the correct result.
     * @covers ::definition
     * @covers ::get_data
     */
    public function test_nudge_notification_edit_submit(): void
    {
        $submission = $this->get_form_data();
        $this->assertNotNull($submission);
        $this->assertInstanceOf(nudge_notification_form_data::class, $submission);

        $this->assertInstanceOf(nudge_notification::class, $submission->notification);

        $this->assertCount(2, $submission->notificationcontents);
        foreach ($submission->notificationcontents as $contents) {
            $this->assertInstanceOf(nudge_notification_content::class, $contents);
        }
    }

    /**
     * @test
     * @testdox clientside validation works as expected
     * @covers ::definition
     * @covers ::get_data
     */
    public function test_nudge_notification_edit_submit_client_validation(): void
    {
        // Initally it works.
        $this->assertInstanceOf(nudge_notification_form_data::class, $this->get_form_data());

        unset($_POST['title']);
        $this->assertStringContainsString(
            get_string('validation_notification_needtitle', 'local_nudge'),
            $this->get_form_display(),
            'A nudge notification should validate for a title'
        );
        $this->assertNull($this->get_form_data());
        $this->mock_nudge_notification_edit_form_post();
        // Also check that the reset works.
        $this->assertInstanceOf(nudge_notification_form_data::class, $this->get_form_data());

        unset($_POST['userfromid']);
        $this->assertStringContainsString(
            get_string('validation_notification_needsender', 'local_nudge'),
            $this->get_form_display(),
            'A nudge notifciation form should validate for a sender'
        );
        $this->assertNull($this->get_form_data());
        $this->mock_nudge_notification_edit_form_post();

        unset($_POST['lang'][0]);
        $this->assertStringContainsString(
            'You must supply a value here.',
            $this->get_form_display(),
            'A nudge notification form should validate each content has a language set'
        );
        $this->assertNull($this->get_form_data());
        $this->mock_nudge_notification_edit_form_post();

        unset($_POST['lang'][1]);
        $this->assertStringContainsString(
            'You must supply a value here.',
            $this->get_form_display(),
            'A nudge notification form should validate each content has a subject set'
        );
        $this->assertNull($this->get_form_data());
        $this->mock_nudge_notification_edit_form_post();

        unset($_POST['body'][0]);
        $this->assertStringContainsString(
            'You must supply a value here.',
            $this->get_form_display(),
            'A nudge notification form should validate each content has a subject set'
        );
        $this->assertNull($this->get_form_data());
        $this->mock_nudge_notification_edit_form_post();
    }

    /**
     * @test
     * @testdox it validates that you can only have one translation for each language
     * @covers ::validation
     * @covers ::get_data
     */
    public function test_nudge_notification_edit_submit_validate_onelang(): void
    {
        $_POST['hiddenrepeat'] = (string) (((int) $_POST['hiddenrepeat']) + 1);
        $_POST['contentid'][] = '7';
        $_POST['lang'][] = 'en';
        $_POST['subject'][] = 'xyz';
        $_POST['body'][] = [
            'text' => 'duplicate',
            'format' => \FORMAT_HTML,
        ];
        $this->assertNull($this->get_form_data());
        $this->assertStringContainsString(
            get_string('validation_notification_duplicatelangs', 'local_nudge'),
            $this->get_form_display(),
            'A nudge notification form should flag the duplicate english translations'
        );
    }

    /**
     * Gets the submited result of a {@see nudge_notification} {@see edit} form.
     *
     * @return nudge_notification_form_data|null
     */
    private function get_form_data(): ?nudge_notification_form_data
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
    private function mock_nudge_notification_edit_form_post(): void
    {
        // phpcs:disable moodle.Files.LineLength
        $_POST = [
            'id' => '0',
            'hiddenrepeat' => '2',
            'contentid' => [
                '3',
                '5'
            ],
            'sesskey' => \sesskey(),
            '_qf__local_nudge_form_nudge_notification_edit' => '1',
            'title' => 'Example Notification',
            'userfromid' => '2',
            'lang' => [
                'fr',
                'en',
            ],
            'subject' => [
                'Example Subject',
                'French Version - Reminder for {course_fullname}'
            ],
            'body' => [
                [
                    'text' => <<<HTML
                    <p dir="ltr"><span style="font-style: normal;">Hi, {user_firstname},</span></p><p dir="ltr"><span style="font-style: normal;"><br></span></p><p dir="ltr"><span style="font-style: normal;">We'd really like to see you complete course: {course_fullname}.</span></p><p dir="ltr"><span style="font-style: normal;">Can you please login via {course_link} and check your completion is up to date.</span></p><p dir="ltr"><span style="font-style: normal;"><br></span></p><p dir="ltr"><span style="font-style: normal;">Thanks, {sender_firstname}.</span></p>
                    HTML,
                    'format' => \FORMAT_HTML
                ],
                [
                    'text' => <<<HTML
                    <p dir="ltr" style="text-align: left;"><em><em></em></em></p><fieldset id="id_translationhdr_0"><div><div id="fitem_id_body_0"><div data-fieldtype="editor"><div><div><div><div><div id="id_body_0editable" contenteditable="true" role="textbox" spellcheck="true" aria-live="off" aria-labelledby="yui_3_17_2_1_1649751013735_115"><p dir="ltr"><span><span style="font-style: normal;"><strong>Hi</strong>, {user_firstname},</span></span></p><p dir="ltr"><span><span style="font-style: normal;"><br></span></span></p><p dir="ltr"><span><span style="font-style: normal;">We'd really like to see you complete the course: <strong>{course_fullname}</strong>.</span></span></p><p dir="ltr"><span><span style="font-style: normal;">Can you please login via {course_link} and check your completion is up to date.</span></span></p><p dir="ltr"><span><span style="font-style: normal;"><br></span></span></p><p dir="ltr"><span><span style="font-style: normal;">Thanks, <strong>{sender_firstname}</strong>.</span></span></p></div></div></div></div></div></div></div></div></fieldset><span></span><br><p></p>
                    HTML,
                    'format' => \FORMAT_HTML
                ]
            ],
            'submitbutton' => 'Save changes',
        ];
        // phpcs:enable moodle.Files.LineLength
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
