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
use coding_exception;
use local_nudge\local\nudge;
use stdClass;
use UnexpectedValueException;

// phpcs:disable moodle.Commenting
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine

/**
 * This tests a {@see abstract_nudge_entity} via {@see nudge} (At the time of testing this has no overrides)
 *
 * @testdox When subclassing a abstract_nudge_entity
 */
class abstract_nudge_entity_test extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();
    }

    public function provide_construct_with_valid_data(): array
    {
        $data = new stdClass();
        $data->courseid = 1;

        return [
            'Empty array' => [
                []
            ],
            'Array with valid data' => [
                [
                    'id' => 5
                ]
            ],
            'Array with valid property' => [
                [
                    'isenabled' => true
                ]
            ],
            'Empty standard class' => [
                (new stdClass())
            ],
            'Standard class with valid property' => [
                $data
            ]
        ];
    }

    /**
     * @test
     * @testdox Supplying a $_dataName constructs without issue.
     * @dataProvider provide_construct_with_valid_data
     * @covers local_nudge\local\abstract_nudge_entity::__construct
     */
    public function test_contruct_with_valid_data($data): void {
        $nudge = new nudge($data);

        $this->assertInstanceOf(nudge::class, $nudge);
    }

    public function provide_construct_with_invalid_data(): array {
        $invalidstdclass = new stdClass();
        $invalidstdclass->propertythatdoesntexist = 1;

        return [
            'Invalid data types' => [
                5, coding_exception::class
            ],
            'Invalid properties on an stdClass' => [
                $invalidstdclass, UnexpectedValueException::class
            ],
            'Invalid properties on an array' => [
                [
                    'propertythatdoesntexist' => 1
                ],
                UnexpectedValueException::class
            ]
        ];
    }

    /**
     * @test
     * @testdox Supplying $_dataName to an entities constructor fails gracefully.
     * @dataProvider provide_construct_with_invalid_data
     * @covers local_nudge\local\abstract_nudge_entity::__construct
     */
    public function test_contruct_with_invalid_data($data, $exception): void
    {
        $this->expectException($exception);

        new nudge($data);
    }

    /**
     * @test
     * @testdox Constructing without data works fine.
     * @covers local_nudge\local\abstract_nudge_entity::__construct
     */
    public function test_contruct_with_no_data(): void {
        $nudge = new nudge();

        $this->assertInstanceOf(nudge::class, $nudge);
        $this->assertEquals($nudge->id, null);
        $this->assertEquals($nudge->courseid, null);
    }

    public function tearDown(): void {
        parent::tearDown();
    }
}
