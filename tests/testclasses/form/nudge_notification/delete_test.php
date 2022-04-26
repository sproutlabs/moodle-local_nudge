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
use local_nudge\form\nudge_notification\delete;
use stdClass;

// phpcs:disable moodle.Files.LineLength
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine

/**
 * @coversDefaultClass \local_nudge\form\nudge_notification\delete
 * @testdox When working with a nudge notification delete form
 */
class delete_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();

        $this->resetAfterTest();
    }

    /**
     * @test
     * @testdox using a delete {@see nudge_notification} form returns a stdClass to delete.
     * @covers ::definition
     */
    public function test_nudge_delete_submit(): void
    {
        $_POST = [
            'id' => '0',
            'courseid' => '0',
            'sesskey' => \sesskey(),
            '_qf__local_nudge_form_nudge_notification_delete' => '1',
            'submitbutton' => 'Delete',
        ];

        $this->assertIsObject($this->get_form_data());
    }

    /**
     * Gets the submited result of a {@see nudge_notification} {@see delete} form.
     *
     * @return stdClass|null
     */
    private function get_form_data(): ?stdClass
    {
        $edit = new delete();
        $this->assertFalse($edit->is_cancelled());
        return $edit->get_data();
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
