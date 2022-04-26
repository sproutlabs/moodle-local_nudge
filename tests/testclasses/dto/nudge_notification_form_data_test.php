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
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine

/**
 * @package     local_nudge\tests\dto
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\testclasses\dto;

use advanced_testcase;
use coding_exception;
use local_nudge\dto\nudge_notification_form_data;
use local_nudge\local\nudge_notification;
use local_nudge\local\nudge_notification_content;
use stdClass;

/**
 * @coversDefaultClass \local_nudge\dto\nudge_notification_form_data
 * @testdox When packing a nudge notification form data transfer object
 */
class nudge_notification_form_data_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @testdox providing invalid data results in a coding exception.
     * @covers ::__construct
     */
    public function test_dto_invalid_exception(): void
    {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage(\sprintf(
            'Coding error detected, it must be fixed by a programmer: You must pass an instance of %s as notificationcontent',
            nudge_notification_content::class
        ));

        // Since this is mostly packaged by {@see nudge_notification::as_notification_form()}
        // throwing a coding exception should only really happen during development.
        new nudge_notification_form_data(
            new nudge_notification(),
            [
                new nudge_notification_content(),
                new stdClass
            ]
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
