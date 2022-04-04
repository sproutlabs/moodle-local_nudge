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
 * DML for {@see nudge}
 *
 * @package     local_nudge\dml
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 */

namespace local_nudge\dml;

use context_course;
use context_system;
use local_nudge\event\nudge_created;
use local_nudge\event\nudge_deleted;
use local_nudge\event\nudge_updated;
use local_nudge\local\nudge;

/**
 * {@inheritDoc}
 * @extends abstract_nudge_db<nudge>
 */
class nudge_db extends abstract_nudge_db {

    /** {@inheritdoc} */
    public static $table = 'local_nudge';

    /** {@inheritdoc} */
    public static $entityclass = nudge::class;

    /**
     * Returns an array of active instances.
     * @return array<nudge>
     */
    public static function get_enabled() {
        return static::get_all_filtered([
            'isenabled' => 1
        ]);
    }

    public static function on_after_create($id): void {
        $creatednudge = (array) self::get_by_id(\intval($id));
        $event = nudge_created::create([
            'context' => ($creatednudge['courseid'] ?? false)
                ? context_course::instance($creatednudge['courseid'])
                : context_system::instance(),
            'objectid' => $id,
            'other' => $creatednudge,
        ]);

        $event->add_record_snapshot(self::$table, (object)(array) $creatednudge);
        $event->trigger();
    }

    public static function on_after_save($id): void {
        $updatednudge = (array) self::get_by_id(\intval($id));
        $event = nudge_updated::create([
            'context' => ($updatednudge['courseid'] ?? false)
                ? context_course::instance($updatednudge['courseid'])
                : context_system::instance(),
            'objectid' => $id,
            'other' => $updatednudge
        ]);

        $event->add_record_snapshot(self::$table, (object)(array) $updatednudge);
        $event->trigger();
    }

    // Ideally this would be after it succeeds.
    public static function on_before_delete($id): void {
        $nudgetobedeleted = (array) self::get_by_id(\intval($id));
        $event = nudge_deleted::create([
            'context' => ($nudgetobedeleted['courseid'] ?? false)
                ? context_course::instance($nudgetobedeleted['courseid'])
                : context_system::instance(),
            'objectid' => $id,
            'other' => $nudgetobedeleted
        ]);

        $event->add_record_snapshot(self::$table, (object)(array) $nudgetobedeleted);
        $event->trigger();
    }
}
