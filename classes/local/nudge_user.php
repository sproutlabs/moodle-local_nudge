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
 * @package     local_nudge\local
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\local;

use local_nudge\local\abstract_nudge_entity;

class nudge_user extends abstract_nudge_entity {

    /**
     * @var int|null Foreign key for has_one to {@see \core\entity\user}.
     */
    public $userid = null;

    /**
     * @var int|null Foreign key for has_one to {@see nudge}.
     */
    public $nudgeid = null;

    /**
     * Used to track individual enrollments for recurring notifications.
     *
     * @var int|null Timestamp representing the last time this user was sent a notification.
     */
    public $recurrancetime = null;
}
