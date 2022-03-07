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
use coding_exception;
use local_nudge\local\nudge;
use local_nudge\dml\nudge_db;

// Lots of disables intentionally doing some silly stuff.
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine
// phpcs:disable moodle.Commenting.InlineComment.TypeHintingMatch
// phpcs:disable moodle.Commenting.InlineComment.NotCapital
// phpcs:disable moodle.Commenting.InlineComment.DocBlock

/**
 * This tests a {@see abstract_nudge_entity} via {@see nudge}.
 *
 * @testdox When subclassing a abstract_nudge_entity
 */
class abstract_nudge_entity_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @testdox Supplying incorrect data to an entities constructor fails gracefully.
     * @small
     * @covers local_nudge\local\abstract_nudge_entity::__construct
     */
    public function test_contruct_with_invalid_data(): void
    {
        $nudge = new nudge();

        $this->assertInstanceOf(nudge::class, $nudge);
        // Inheritied.
        $this->assertEquals($nudge->id, null);
        // Regular.
        $this->assertEquals($nudge->courseid, null);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Coding error detected, it must be fixed by a programmer: You must provide valid data to local_nudge\local\abstract_nudge_entity::__construct to wrap a instance of local_nudge\local\abstract_nudge_entity');

        // Invalid constructor type.
        /** @phpstan-ignore-next-line */
        $nudge = new nudge(5);
    }


    public function tearDown(): void {
        parent::tearDown();
    }
}
