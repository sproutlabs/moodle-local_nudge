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
 * @package     local_nudge
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

use local_nudge\dml\nudge_db;

class backup_local_nudge_plugin extends backup_local_plugin {

    protected function define_course_plugin_structure() {
        $plugin = $this->get_plugin_element(null);

        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $nudges = new backup_nested_element('nudges');
        $elements = [
            'courseid',
            // We can just directly reuse these has ones since the notifications and their contents exists in a site context.
            'linkedlearnernotificationid',
            'linkedmanagernotificationid',
            'title',
            // No need to backup isenabled since it will always be disabled.
            'reminderrecipient',
            'remindertype',
            'remindertypefixeddate',
            'remindertypeperiod',
        ];
        $nudge = new backup_nested_element('nudge', ['id'], $elements);

        $plugin->add_child($pluginwrapper);
        $pluginwrapper->add_child($nudges);
        $nudges->add_child($nudge);

        $courseid = backup_helper::is_sqlparam($this->get_setting_value(backup::VAR_COURSEID));
        $nudge->set_source_table(nudge_db::$table, ['courseid' => $courseid]);

        $nudge->annotate_ids('courseid', 'courseid');

        return $plugin;
    }
}
