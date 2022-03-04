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
 * DML for {@see nudge_user}
 *
 * @package     local_nudge\dml
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\dml;

use local_nudge\local\nudge_user;

defined('MOODLE_INTERNAL') || die();

/**
 * {@inheritDoc}
 * @extends abstract_nudge_db<nudge_user>
 */
class nudge_user_db extends abstract_nudge_db
{
    /** {@inheritdoc} */
    protected static $table = 'nudge_user';

    /** {@inheritdoc} */
    protected static $entity_class = nudge_user::class;
}
