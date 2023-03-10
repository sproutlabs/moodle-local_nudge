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
// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore

namespace local_nudge\event;

use core\event\base;
use local_nudge\dml\nudge_db;
use moodle_url;

/**
 * @package     local_nudge\event
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 */
class nudge_created extends base {
    /**
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function get_description() {
        // phpcs:ignore
        return "The user with the ID: '{$this->userid}' created nudge with the ID of: '{$this->other['id']}' for the course ID of: '{$this->other['courseid']}'.";
    }

    /**
     * @codeCoverageIgnore
     *
     * @return moodle_url
     */
    public function get_url() {
        return new moodle_url(
            '/local/nudge/edit_nudge.php',
            [
                'id' => $this->other['id'],
                'courseid' => $this->other['courseid']
            ]
        );
    }

    /**
     * @return void
     */
    protected function init() {
        $this->data['objecttable']  = nudge_db::$table;
        $this->data['crud']         = 'c';
        $this->data['edulevel']     = self::LEVEL_OTHER;
    }
}
