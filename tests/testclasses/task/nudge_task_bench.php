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

namespace local_nudge\testclasses\task;

use advanced_testcase;
use local_nudge\dml\nudge_db;
use local_nudge\dml\nudge_notification_content_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;
use local_nudge\local\nudge_notification_content;
use local_nudge\task\nudge_task;

// phpcs:disable moodle.Commenting
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.BraceOnNewLine

/**
 * @testdox When the nudge_tasks runs it should
 */
class nudge_task_bench extends advanced_testcase {
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @testdox Benchmarking recurring nudge
     */
    public function test_send_benchmark_recurring(): void
    {
        $usercount = 4_000;

        // -------------------------------
        //          SETUP TEST
        // -------------------------------
        /** @var \core_config $CFG */
        global $CFG;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $sink = $this->redirectMessages();

        // -------------------------------
        //          SETUP DATA
        // -------------------------------

        $time = \time();
        $CFG->nudgemocktime = $time;

        $course = $this->getDataGenerator()->create_course([]);
        $sender = $this->getDataGenerator()->create_user();
        for ($i = $usercount; $i--; $i != 0) {
            $this->getDataGenerator()->create_and_enrol(
                $course,
                'student',
                null,
                'manual',
                $time
            );
        }

        // TODO: dataGenerators for nudge, notification and contents.
        $notification = nudge_notification_db::create_or_refresh(
            new nudge_notification(['userfromid' => $sender->id])
        );
        nudge_notification_content_db::create_or_refresh(new nudge_notification_content([
            'nudgenotificationid' => $notification->id
        ]));

        nudge_db::create_or_refresh(new nudge([
            'isenabled' => 1,
            'courseid' => $course->id,
            'linkedlearnernotificationid' => $notification->id,
            'remindertype' => nudge::REMINDER_DATE_RELATIVE_ENROLLMENT,
            // Send reminder 2 minutes after enrollment.
            'remindertypeperiod' => \MINSECS * 2
        ]));

        // -------------------------------
        //      PERFORM ASSERTIONS
        // -------------------------------

        (new nudge_task)->execute();

        $this->assertEquals(0, $sink->count(), 'There should be an no message to any user yet.');

        $CFG->nudgemocktime += (\MINSECS * 2);

        $start = \time();
        (new nudge_task)->execute();
        $total = \time() - $start;
        printf(
            \str_repeat(\PHP_EOL, 2) .
            '------' . \PHP_EOL .
            'Processed a recurring nudge with %s users' . \PHP_EOL .
            'Total time processing: %s seconds or %s minutes' . \PHP_EOL .
            '------' .
            \str_repeat(\PHP_EOL, 2),
            $usercount,
            $total,
            $total / 60
        );

        $this->assertEquals($usercount, $sink->count(), 'There was an inconsistant ammount of message sent.');
    }

    /**
     * @testdox Benchmarking fixed nudge
     */
    public function test_send_benchmark_fixed(): void
    {
        $usercount = 4_000;

        // -------------------------------
        //          SETUP TEST
        // -------------------------------
        /** @var \core_config $CFG */
        global $CFG;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $sink = $this->redirectMessages();

        // -------------------------------
        //          SETUP DATA
        // -------------------------------

        $time = \time();
        $CFG->nudgemocktime = $time;

        $course = $this->getDataGenerator()->create_course([]);
        $sender = $this->getDataGenerator()->create_user();
        for ($i = $usercount; $i--; $i != 0) {
            $this->getDataGenerator()->create_and_enrol(
                $course,
                'student',
                null,
                'manual',
                $time
            );
        }

        // TODO: dataGenerators for nudge, notification and contents.
        $notification = nudge_notification_db::create_or_refresh(
            new nudge_notification(['userfromid' => $sender->id])
        );
        nudge_notification_content_db::create_or_refresh(new nudge_notification_content([
            'nudgenotificationid' => $notification->id
        ]));

        nudge_db::create_or_refresh(new nudge([
            'isenabled' => 1,
            'courseid' => $course->id,
            'linkedlearnernotificationid' => $notification->id,
            'remindertype' => nudge::REMINDER_DATE_INPUT_FIXED,
            // Send reminder 2 minutes after enrollment.
            'remindertypefixeddate' => $time + 1
        ]));

        // -------------------------------
        //      PERFORM ASSERTIONS
        // -------------------------------

        (new nudge_task)->execute();

        $this->assertEquals(0, $sink->count(), 'There should be an no message to any user yet.');

        $CFG->nudgemocktime += 1;

        $start = \time();
        (new nudge_task)->execute();
        $total = \time() - $start;
        printf(
            \str_repeat(\PHP_EOL, 2) .
            '------' . \PHP_EOL .
            'Processed a recurring nudge with %s users' . \PHP_EOL .
            'Total time processing: %s seconds or %s minutes' . \PHP_EOL .
            '------' .
            \str_repeat(\PHP_EOL, 2),
            $usercount,
            $total,
            $total / 60
        );

        $this->assertEquals($usercount, $sink->count(), 'There was an inconsistant ammount of message sent.');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
