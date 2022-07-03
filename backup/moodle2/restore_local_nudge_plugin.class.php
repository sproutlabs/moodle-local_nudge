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

use local_nudge\dml\nudge_db;
use local_nudge\local\nudge;

/**
 * @package     local_nudge
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

class restore_local_nudge_plugin extends restore_local_plugin {

    protected function define_course_plugin_structure() {
        return [
            new restore_path_element('local_nudge_nudge', $this->get_pathfor('/nudges/nudge'))
        ];
    }

    /**
     * @param array $data
     */
    public function process_local_nudge_nudge($data) {
        $nudge = new nudge($data);
        // Nudges should always restore disabled. This is to prevent unexpected messages around relative timings.
        $nudge->isenabled = false;
        $nudge->courseid = $this->get_mappingid('course', $data['courseid']);

        // Created by and timecreated will also be reset due to the id unset which is nice.
        unset($nudge->id);
        nudge_db::save($nudge);
    }
}
