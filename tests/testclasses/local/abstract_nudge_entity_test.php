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
use local_nudge\dml\nudge_db;
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
     * @covers \local_nudge\local\abstract_nudge_entity::__construct
     */
    public function test_contruct_with_valid_data($data): void
    {
        $nudge = new nudge($data);

        $this->assertInstanceOf(nudge::class, $nudge);
    }

    public function provide_construct_with_invalid_data(): array
    {
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
     * @covers \local_nudge\local\abstract_nudge_entity::__construct
     */
    public function test_contruct_with_invalid_data($data, $exception): void
    {
        $this->expectException($exception);

        new nudge($data);
    }

    /**
     * @test
     * @testdox Constructing without data works fine.
     * @covers \local_nudge\local\abstract_nudge_entity::__construct
     */
    public function test_contruct_with_no_data(): void
    {
        $nudge = new nudge();

        $this->assertInstanceOf(nudge::class, $nudge);
        $this->assertEquals($nudge->id, null);
        $this->assertEquals($nudge->courseid, null);
    }

    // TODO: Some of these may be better placed in DML tests.

    /**
     * @test
     * @testdox Saving will result in createdby being set to the current user.
     * @covers \local_nudge\dml\nudge_db::save
     */
    public function test_set_and_update_createdby(): void
    {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $nudge = new nudge();
        $nudge = nudge_db::create_or_refresh($nudge);

        $this->assertIsInt($nudge->createdby);
        $this->assertEquals($user->id, $nudge->createdby);

        $this->setUser();

        $whatuser = new nudge();
        $whatuser = nudge_db::create_or_refresh($whatuser);

        $this->assertIsInt($whatuser->createdby);
        $this->assertEquals(0, $whatuser->createdby);
    }

    /**
     * @test
     * @testdox Createdby will not be changed.
     * @covers \local_nudge\dml\nudge_db::save
     */
    public function test_immutable_createdby(): void
    {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $nudge = new nudge();
        $nudge = nudge_db::create_or_refresh($nudge);

        $this->assertEquals($user->id, $nudge->createdby);

        $nudge->createdby = 5;
        $nudge = nudge_db::create_or_refresh($nudge);

        $this->assertNotEquals(5, $nudge->createdby);
    }

    /**
     * @test
     * @testdox Saving will result in timecreated being set to current time.
     * @covers \local_nudge\dml\nudge_db::save
     */
    public function test_set_and_update_timecreated(): void
    {
        /** @var \core_config $CFG */
        global $CFG;

        $this->resetAfterTest();

        $time = \time();
        $CFG->nudgemocktime = $time;

        $nudge = new nudge();
        $nudge = nudge_db::create_or_refresh($nudge);

        $this->assertIsInt($nudge->timecreated);
        $this->assertEquals($time, $nudge->timecreated);
    }

    /**
     * @test
     * @testdox Timecreated will not be changed.
     * @covers \local_nudge\dml\nudge_db::save
     */
    public function test_immutable_timecreated(): void
    {
        /** @var \core_config $CFG */
        global $CFG;

        $this->resetAfterTest();

        $time = \time();
        $CFG->nudgemocktime = $time;

        $nudge = new nudge();
        $nudge = nudge_db::create_or_refresh($nudge);

        $this->assertEquals($time, $nudge->timecreated);

        $nudge->timecreated = 5;
        $nudge = nudge_db::create_or_refresh($nudge);

        $this->assertNotEquals(5, $nudge->timecreated);
    }

    /**
     * @test
     * @testdox When saving lastmodifiedby will be set to the current user and change when updated.
     * @covers \local_nudge\dml\nudge_db::save
     */
    public function test_set_and_update_lastmodifedby(): void
    {
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $this->setUser($user1);

        $nudge = new nudge();
        $nudge = nudge_db::create_or_refresh($nudge);

        $this->assertIsInt($nudge->lastmodifiedby);
        $this->assertEquals($user1->id, $nudge->lastmodifiedby);

        $this->setUser($user2);

        $nudge = nudge_db::create_or_refresh($nudge);

        $this->assertIsInt($nudge->lastmodifiedby);
        $this->assertEquals($user2->id, $nudge->lastmodifiedby);
    }

    /**
     * @test
     * @testdox When saving lastmodified will be set to the current time and changed when updated.
     * @covers \local_nudge\dml\nudge_db::save
     */
    public function test_set_and_update_lastmodifed(): void
    {
        /** @var \core_config $CFG */
        global $CFG;

        $this->resetAfterTest();

        $time = \time();
        $CFG->nudgemocktime = $time;

        $nudge = new nudge();
        $nudge = nudge_db::create_or_refresh($nudge);

        $this->assertIsInt($nudge->lastmodified);
        $this->assertEquals($time, $nudge->lastmodified);

        $newtime = \time();
        $CFG->nudgemocktime = $newtime;

        $nudge = nudge_db::create_or_refresh($nudge);

        $this->assertIsInt($nudge->lastmodified);
        $this->assertEquals($newtime, $nudge->lastmodified);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
