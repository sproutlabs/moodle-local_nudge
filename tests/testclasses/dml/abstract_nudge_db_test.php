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
// phpcs:disable Squiz.PHP.CommentedOutCode
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine

/**
 * @package     local_nudge\tests\dml
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\testclasses\dml;

use advanced_testcase;
use coding_exception;
use local_nudge\dml\nudge_db;
use local_nudge\dml\nudge_notification_content_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;
use local_nudge\local\nudge_notification_content;

/**
 * @coversDefaultClass \local_nudge\dml\abstract_nudge_db
 * @testdox Whilst subclassing and using a abstract nudge database modification layer
 */
class abstract_nudge_db_test extends advanced_testcase {
    public function setUp(): void
    {
        /** @var \core_config $CFG */
        global $CFG;

        parent::setUp();

        $this->resetAfterTest();

        // Ignore time for these tests.
        $CFG->nudgemocktime = 1;
    }

    /**
     * @test
     * @testdox calling create or refresh correctly changes the database state and returns an instance of nudge.
     * @covers ::create_or_refresh
     */
    public function test_create_or_refresh(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        $nudge = nudge_db::create_or_refresh(new nudge());
        $this->assertInstanceOf(nudge::class, $nudge);
        $this->assertEquals(1, $DB->count_records(nudge_db::$table));

        $nudge->title = 'xyz';
        $resultingnudge = nudge_db::create_or_refresh($nudge);
        $this->assertInstanceOf(nudge::class, $resultingnudge);
        $this->assertEquals('xyz', $DB->get_field(nudge_db::$table, 'title', ['id' => $nudge->id]));

        $this->assertEquals($nudge, $resultingnudge);
    }

    /**
     * @test
     * @testdox calling get by id will return the correct record.
     * @covers ::get_by_id
     */
    public function test_get_by_id(): void
    {
        $nudge = nudge_db::create_or_refresh(new nudge());

        $resultingnudge = nudge_db::get_by_id($nudge->id);

        $this->assertEquals($nudge, $resultingnudge);
    }

    /**
     * @test
     * @testdox providing a negative integer to get by id will throw an exception
     * @covers ::get_by_id
     */
    public function test_get_by_id_invalid(): void
    {
        $this->expectException(coding_exception::class);
        nudge_db::get_by_id(-1);
    }

    public function provide_get_all_classes(): array
    {
        return [
            'nudge' => [
                nudge::class, nudge_db::class
            ],
            'nudge notification' => [
                nudge_notification::class, nudge_notification_db::class
            ],
        ];
    }

    /**
     * @test
     * @testdox using get all will correctly return all $_dataName instances saved to the database.
     * @covers ::get_all
     * @dataProvider provide_get_all_classes
     */
    public function test_get_all(string $class, string $dml): void
    {
        $inital = nudge_db::get_all();
        $this->assertIsArray($inital);
        $this->assertCount(0, $inital);

        /** @var class-string|string $class */
        /** @var class-string|string $dml */

        /** @var array<abstract_nudge_entity> $instancearray */
        $instancearray = [];
        for ($i = 0; $i != 5; $i++) {
            $instancearray[] = $dml::create_or_refresh(new $class([
                'title' => 'instance' . (string) $i
            ]));
        }

        $resultarray = $dml::get_all();
        $this->assertIsArray($resultarray);

        for ($i = 0; $i != 5; $i++) {
            $this->assertArrayHasKey($i, $instancearray);
            $this->assertArrayHasKey($i, $resultarray);

            $this->assertInstanceOf($class, $resultarray[$i]);
            $this->assertEquals($instancearray[$i], $resultarray[$i]);
        }
    }

    /**
     * @test
     * @testdox calling get filtered will return the desired record.
     * @covers ::get_filtered
     */
    public function test_get_filtered(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        $this->assertNull(nudge_db::get_filtered(['id' => 9999999]));

        $nudge = new nudge();
        $nudge->isenabled = true;
        $nudge = nudge_db::create_or_refresh($nudge);

        $nottherightnudge = new nudge();
        nudge_db::save($nottherightnudge);

        $this->assertEquals(2, $DB->count_records(nudge_db::$table));

        $resultingnudge = nudge_db::get_filtered([
            'isenabled' => true
        ]);

        $this->assertInstanceOf(nudge::class, $resultingnudge);
        $this->assertEquals($nudge, $resultingnudge);
    }

    /**
     * @test
     * @testdox calling get all filtered finds the correct records.
     * @covers ::get_all_filtered
     */
    public function test_get_all_filtered(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        $inital = nudge_db::get_all_filtered(['id' => 9999999]);
        $this->assertIsArray($inital);
        $this->assertCount(0, $inital);

        /** @var array<nudge> $disabledinstances */
        $disabledinstances = [];

        for ($i = 0; $i != 5; $i++) {
            // Some enabled instances.
            nudge_db::create_or_refresh(new nudge([
                'isenabled' => true
            ]));

            $rng = (string) \rand() & 5;
            $disabledinstances[] = nudge_db::create_or_refresh(new nudge([
                'title' => "disabled instance {$rng}"
            ]));
        }

        $this->assertEquals(10, $DB->count_records(nudge_db::$table));
        $this->assertEquals(5, $DB->count_records(nudge_db::$table, ['isenabled' => true]));
        $this->assertEquals(5, $DB->count_records(nudge_db::$table, ['isenabled' => false]));

        $resultarray = nudge_db::get_all_filtered([
            'isenabled' => false
        ]);
        $this->assertCount(5, $resultarray);

        for ($i = 0; $i != 5; $i++) {
            $this->assertArrayHasKey($i, $resultarray);
            $this->assertInstanceOf(nudge::class, $resultarray[$i]);

            $this->assertEquals($disabledinstances, $resultarray);
        }
    }

    /**
     * @test
     * @testdox calling get sql with a simple queries will correctly wrap the record with an entity instance.
     * @covers ::get_sql
     */
    public function test_get_sql(): void
    {
        /** @var \moodle_database $DB */
        global $DB;
        $table = nudge_db::$table;
        $basesql = <<<SQL
            SELECT
                n.*
            FROM
                {{$table}} as n
            WHERE
                [[WHERE]]
        SQL;

        $nudge = nudge_db::create_or_refresh(new nudge(['title' => 'filterable']));
        $this->assertEquals(1, $DB->count_records($table));

        $resultingnudge = nudge_db::get_sql(\strtr(
            $basesql,
            ['[[WHERE]]' => "n.id = {$nudge->id}"]
        ));
        $this->assertInstanceOf(nudge::class, $resultingnudge);
        $this->assertEquals($nudge, $resultingnudge);

        $paramnudge = nudge_db::get_sql(\strtr(
            $basesql,
            ['[[WHERE]]' => 'n.id = :id']
        ), ['id' => $nudge->id]);
        $this->assertInstanceOf(nudge::class, $paramnudge);
        $this->assertEquals($nudge, $paramnudge);

        $nullresult = nudge_db::get_sql(\strtr(
            $basesql,
            ['[[WHERE]]' => 'n.id = 999999999']
        ));
        $this->assertNull($nullresult);
    }

    /**
     * @test
     * @testdox calling get all sql with a filtering query will return the correctly filtered and wrapped instances.
     * @covers ::get_all_sql
     */
    public function test_get_all_sql(): void
    {
        /** @var \moodle_database $DB */
        global $DB;
        $table = nudge_db::$table;

        $inital = nudge_db::get_all_filtered(['id' => 9999999]);
        $this->assertIsArray($inital);
        $this->assertCount(0, $inital);

        /** @var array<nudge> $disabledinstances */
        $disabledinstances = [];

        for ($i = 0; $i != 5; $i++) {
            // Some enabled instances.
            nudge_db::create_or_refresh(new nudge([
                'isenabled' => true
            ]));

            $rng = (string) \rand() & 5;
            $disabledinstances[] = nudge_db::create_or_refresh(new nudge([
                'title' => "disabled instance {$rng}"
            ]));
        }

        $this->assertEquals(10, $DB->count_records($table));
        $this->assertEquals(5, $DB->count_records($table, ['isenabled' => true]));
        $this->assertEquals(5, $DB->count_records($table, ['isenabled' => false]));

        $resultarray = nudge_db::get_all_sql(<<<SQL
            SELECT
                n.*
            FROM
                {{$table}} as n
            WHERE
                n.isenabled = 0
        SQL);
        $this->assertCount(5, $resultarray);

        for ($i = 0; $i != 5; $i++) {
            $this->assertArrayHasKey($i, $resultarray);
            $this->assertInstanceOf(nudge::class, $resultarray[$i]);

            $this->assertEquals($disabledinstances, $resultarray);
        }
    }

    /**
     * @test
     * @testdox calling save to persist a record works as expected.
     * @covers ::save
     */
    public function test_save_persists(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        $this->setUser($this->getDataGenerator()->create_user());

        $nudge = new nudge();
        $nudge->title = 'custom';

        $nudgeid = nudge_db::save($nudge);

        // The record being saved should be cloned so these should not be changed.
        $this->assertEquals(0, $nudge->lastmodified);
        $this->assertEquals(0, $nudge->lastmodifiedby);
        $this->assertEquals(0, $nudge->createdby);
        $this->assertEquals(0, $nudge->timecreated);

        $resultingnudge = $DB->get_record(nudge_db::$table, ['id' => $nudgeid]);
        $this->assertIsObject($resultingnudge);

        $this->assertEquals($nudge->title, $resultingnudge->title);
        // Control fields should be present on the new record.
        $this->assertGreaterThan(0, $resultingnudge->lastmodified);
        $this->assertGreaterThan(0, $resultingnudge->lastmodifiedby);
        $this->assertGreaterThan(0, $resultingnudge->createdby);
        $this->assertGreaterThan(0, $resultingnudge->timecreated);
    }

    /**
     * @test
     * @testdox calling save to update a record correctly changes the database values
     * @covers ::save
     */
    public function test_save_updates(): void
    {
        /** @var \moodle_database $DB */
        global $DB;

        $this->setUser($this->getDataGenerator()->create_user());

        $nudge = new nudge();
        $nudge->title = 'custom';

        $nudgeid = nudge_db::save($nudge);

        $resultingnudge = $DB->get_record(nudge_db::$table, ['id' => $nudgeid]);
        $this->assertIsObject($resultingnudge);
        $this->assertIsString($resultingnudge->title);
        $this->assertEquals('custom', $resultingnudge->title);

        // Should be able to instance from this stdClass.
        $resultingnudge->title = 'new title';
        nudge_db::save(new nudge($resultingnudge));

        $this->assertEquals(1, $DB->count_records(nudge_db::$table), 'Saving should update the existing record');
        $updatednudge = $DB->get_record(nudge_db::$table, ['id' => $nudgeid]);

        $this->assertIsObject($updatednudge);
        $this->assertEquals('new title', $updatednudge->title);
    }

    /**
     * @test
     * @testdox calling save will result in the correct hooks being called
     * @covers ::save
     */
    public function test_save_calls_hooks(): void
    {
        $this->markTestIncomplete('TODO');
    }

    // The delete functions are not covered as they are rather shallow wrappers just provided for consistency.

    public function provide_populate_defaults_classes(): array
    {
        return [
            'nudge' => [
                nudge::class, nudge_db::class
            ],
            'nudge notification' => [
                nudge_notification::class, nudge_notification_db::class
            ],
            'nudge notification content' => [
                nudge_notification_content::class, nudge_notification_content_db::class
            ],
        ];
    }

    /**
     * @test
     * @testdox manually populating defaults for a $_dataName instance using the public API works.
     * @covers ::populate_defaults
     * @dataProvider provide_populate_defaults_classes
     */
    public function test_populate_defaults(string $class, string $dml): void
    {
        /** @var class-string|string $class */
        /** @var class-string|string $dml */

        $instance = new $class();
        $instancevars = array_keys(get_class_vars($class));

        foreach ($instancevars as $prop) {
            // 'Loosely' not set, using booleans since null casting was removed.
            $this->assertFalse((bool) $instance->{$prop});
        }

        $dml::populate_defaults($instance);

        foreach ($instancevars as $prop) {
            $defaultval = $class::DEFAULTS[$prop] ?? false;
            if ($defaultval) {
                $this->assertEquals($defaultval, $instance->{$prop});
            }
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
