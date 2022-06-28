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

namespace local_nudge;

defined('MOODLE_INTERNAL') || die();

/** @var \core_config $CFG */
global $CFG;

require_once(__DIR__.'/../lib.php');
require_once($CFG->dirroot.'/user/profile/definelib.php');
require_once($CFG->dirroot.'/user/profile/field/text/define.class.php');

use advanced_testcase;
use core_user;
use Exception;
use Generator;
use local_nudge\dml\nudge_db;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;
use profile_define_text;
use ReflectionException;

/**
 * @testdox From local_nudge's library
 */
class lib_test extends advanced_testcase {

    public function setUp(): void {
        parent::setUp();
    }

    /**
     * @test
     * @testdox the hook to delete nudges upon their linked courses deletion works.
     * @covers ::local_nudge_pre_course_delete
     */
    public function test_local_nudge_pre_course_delete(): void
    {
        $this->resetAfterTest();

        $count = 5;

        /** @var \moodle_database $DB */
        global $DB;

        $course = $this->getDataGenerator()->create_course();

        for ($i = $count; $i--;) {
            $nudge = new nudge();
            $nudge->courseid = $course->id;
            nudge_db::save($nudge);
        }

        $this->assertEquals($count, $DB->count_records(nudge_db::$table));
        $this->assertCount($count, nudge_db::get_all_filtered([
            'courseid' => $course->id
        ]));

        \ob_start();
        \delete_course($course);
        $this->assertStringContainsString(
            "Deleted - {$count} attached Nudges",
            \ob_get_clean(),
            'Nudges should be deleted'
        );

        $this->assertEquals(0, $DB->count_records(nudge_db::$table));
        $this->assertCount(0, nudge_db::get_all_filtered([
            'courseid' => $course->id
        ]));
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function provide_nudge_scaffold_select_from_constants(): array {
        return [
            'Class that doesn\'t exist' => [
                '\\fake\\class',
                'sss',
                ReflectionException::class
            ],
            'Nudge\'s REMINDER_DATE' => [
                nudge::class,
                'REMINDER_DATE',
                [
                    'fixed' => 'Reminder Date input fixed',
                    'courseend' => 'Reminder Date relative Course end',
                    'enrollment' => 'Reminder Date relative enrollment',
                    'enrollmentrecurring' => 'Reminder Date relative enrollment recurring',
                    'coursecompletion' => 'Remind on Course completion',
                ]
            ],
            'Nudge\'s REMINDER_RECIPIENT' => [
                nudge::class,
                'REMINDER_RECIPIENT',
                [
                    'learner' => 'The Learner',
                    'managers' => 'The Learner\'s Managers',
                    'both' => 'Both the Learner and their Managers',
                ]
            ]
        ];
    }

    /**
     * @test
     * @testdox Providing $_dataName to nudge_scaffold_select_from_constants returns the expected result.
     * @dataProvider provide_nudge_scaffold_select_from_constants
     * @covers ::nudge_scaffold_select_from_constants
     */
    public function test_nudge_scaffold_select_from_constants($class, $filter, $expected): void {
        if (\is_a($expected, Exception::class, true)) {
            $this->expectException($expected);
            nudge_scaffold_select_from_constants($class, $filter);
            return;
        }

        $result = nudge_scaffold_select_from_constants($class, $filter);

        $this->assertSame($expected, $result);
    }

    /**
     * @test
     * @testdox Calling nudge_hydrate_notification_template works with duplicate variables and data from moodle.
     * @covers ::nudge_hydrate_notification_template
     */
    public function test_nudge_hydrate_notification_template(): void {
        /** @var \core_config $CFG */
        global $CFG;

        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $courselink = $CFG->wwwroot . "/course/view.php?id=" . $course->id;
        $userfrom = core_user::get_noreply_user();
        $notification = new nudge_notification();

        $content = '1{user_firstname}{user_firstname}2{user_lastname}3{course_fullname}'
            . '4{course_shortname}5{course_link}6{sender_firstname}'
            . '7{sender_lastname}8{sender_email}9{notification_title}10{course_enddate}';

        $result = nudge_hydrate_notification_template(
            $content,
            $user,
            $course,
            $userfrom,
            $notification
        );

        $formattedcourseenddate = date(nudge::DATE_FORMAT_NICE, $course->enddate);
        $this->assertSame(
            "1{$user->firstname}{$user->firstname}2{$user->lastname}3{$course->fullname}"
                . "4{$course->shortname}5{$courselink}6{$userfrom->firstname}"
                . "7{$userfrom->lastname}8{$userfrom->email}9{$notification->title}10{$formattedcourseenddate}",
            $result
        );
    }

    /**
     * @test
     * @testdox TODO totara testing?
     * @covers ::nudge_get_email_message
     */
    public function test_nudge_get_email_message(): void
    {
        /** @var \core_config $CFG */
        global $CFG;

        if (!isset($CFG->totara_version)) {
            $this->markTestIncomplete('TODO TOTARA');
        } else {
            $this->markTestIncomplete('TODO MOODLe');
        }
    }

    /**
     * @return \Generator<string, mixed>
     */
    public function provide_nudge_moodle_get_manager_for_user(): Generator {
        yield 'match on email address should work' => [
            'customemail',
            'email',
            'manager@example.org',
            'manager@example.org',
            true
        ];
        yield 'mismatch on email address should fail' => [
            'customemail2',
            'email',
            'manager2@example.com',
            'manager@example.com',
            false
        ];
        yield 'match on idnumber should work' => [
            'customidnumberfield',
            'idnumber',
            '233',
            '233',
            true
        ];
        yield 'mismatch on idnumber should fail' => [
            'idnumberfield',
            'idnumber',
            '233',
            '234',
            false
        ];
        yield 'match on database id should work' => [
            'manageridnumberfield',
            'id',
            'valid',
            'valid',
            true
        ];
        yield 'match on email address should be case in-sensitive' => [
            'managersemailadressfield',
            'email',
            'mAnager@example.com',
            'manager@example.com',
            true
        ];
    }

    /**
     * @test
     * @testdox calling nudge_moodle_get_manager_for_user with a $_dataName.
     * @dataProvider provide_nudge_moodle_get_manager_for_user
     * @covers ::nudge_moodle_get_manager_for_user
     */
    public function test_nudge_moodle_get_manager_for_user(
        string $matchwithfield,
        string $matchonfield,
        string $matchwithvalue,
        string $matchonvalue,
        bool $shouldmatch
    ): void {
        // -----------------
        //      SETUP
        // -----------------
        $this->resetAfterTest();

        // Setup which fields to use.
        set_config('managermatchwithfield', $matchwithfield, 'local_nudge');
        set_config('managermatchonfield', $matchonfield, 'local_nudge');

        $this->create_profile_field($matchwithfield);

        /** @var \moodle_database $DB */
        global $DB;

        // We populate the records below to keep logic clean.
        /** @var \core\entity\user|stdClass $user */
        $user = $this->getDataGenerator()->create_user();
        /** @var \core\entity\user|stdClass $manager */
        $manager = $this->getDataGenerator()->create_user();

        // ---------------------
        //      PROFILE DATA
        // ---------------------
        // If we are testing a auto increment primary key we can't hardcode it above.
        if ($matchonfield === 'id') {
            $user->{"profile_field_{$matchwithfield}"} = $manager->id;
            \profile_save_data($user);
        } else {
            // Else we need to ensure that the manager has the identifer and the user's field (may) point to that.
            $manager->{$matchonfield} = $matchonvalue;
            $DB->update_record('user', $manager);

            $user->{"profile_field_{$matchwithfield}"} = $matchwithvalue;
            profile_save_data($user);
        }

        // ---------------------
        //         TEST
        // ---------------------
        $foundmanager = nudge_moodle_get_manager_for_user($user);

        if ($shouldmatch) {
            $this->assertIsObject($foundmanager);
            $this->assertEquals($manager, $foundmanager);
        } else {
            $this->assertNull($foundmanager);
        }
    }

    /**
     * Pretty much covers the IGNORE_MULTIPLE of get_record works with this custom field matching.
     *
     * @test
     * @testdox When matching multiple managers with nudge_moodle_get_manager_for_user only one should return (ORDER BY ID).
     * @covers ::nudge_moodle_get_manager_for_user
     */
    public function test_nudge_moodle_manager_single_return(): void
    {
        $this->resetAfterTest();

        set_config('managermatchwithfield', 'manageremail', 'local_nudge');
        set_config('managermatchonfield', 'email', 'local_nudge');

        $this->create_profile_field('manageremail');

        $user = $this->getDataGenerator()->create_user(['profile_field_manageremail' => 'bothmanagers@example.org']);
        $managera = $this->getDataGenerator()->create_user(['email' => 'bothmanagers@example.org']);
        $this->getDataGenerator()->create_user(['email' => 'bothmanagers@example.org']);

        $foundmanager = nudge_moodle_get_manager_for_user($user);

        $this->assertIsObject($foundmanager);
        $this->assertEquals(
            $managera,
            $foundmanager,
            'Multiple managers in MOODLE should return the first in order of creation (Primary key\'s auto increment).'
        );
    }

    /**
     * @test
     * @testdox TODO totara testing?
     * @covers ::nudge_totara_get_managers_for_user
     *
     * // phpcs:ignore
     * @requires function (TODO: Find a totara only function. using @requires function looks like the easiest way to test conditionally)
     */
    public function test_nudge_totara_get_managers_for_user(): void
    {
        $this->markTestIncomplete('TODO');
    }

    /**
     * @internal Creates a profile field.
     * @param string $shortname
     * @return void
     */
    private function create_profile_field($shortname) {
        /**
         * Create the custom profile fields.
         */
        (new profile_define_text())->define_save((object)[
            'id' => 0,
            'action' => 'editfield',
            'datatype' => 'text',
            'shortname' => $shortname,
            'name' => 'testname',
            'description' => '',
            'required' => '0',
            'locked' => '0',
            'forceunique' => '0',
            'signup' => '0',
            'visible' => '2',
            'categoryid' => '1',
            'defaultdata' => '',
            'param1' => 30,
            'param2' => 2048,
            'param3' => '0',
            'param4' => '',
            'param5' => '',
            'submitbutton' => 'Save changes',
            'descriptionformat' => '1',
        ]);
    }

    public function tearDown(): void {
        parent::tearDown();
    }
}
