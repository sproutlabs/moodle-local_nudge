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
 * @package     local_nudge\tests
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\testclasses\local;

use advanced_testcase;
use local_nudge\dml\nudge_user_db;
use local_nudge\local\nudge_user;

/**
 * @coversDefaultClass \local_nudge\local\nudge_user
 * @testdox When using a nudge user entity
 */
class nudge_user_test extends advanced_testcase {

    public function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();
    }

    /**
     * @test
     * @testdox Creating a new instance will return sane correctly typed defaults.
     * @covers ::cast_fields
     */
    public function test_defaults_casted() {
        $nudgeuser = nudge_user_db::create_or_refresh(new nudge_user([
            'userid' => 1,
            'nudgeid' => 1,
        ]));

        $this->assertIsInt($nudgeuser->userid);
        $this->assertEquals(1, $nudgeuser->userid);

        $this->assertIsInt($nudgeuser->nudgeid);
        $this->assertEquals(1, $nudgeuser->nudgeid);

        $this->assertIsInt($nudgeuser->recurrancetime);
        $this->assertEquals(0, $nudgeuser->recurrancetime);
    }

    public function tearDown(): void {
        parent::tearDown();
    }
}
