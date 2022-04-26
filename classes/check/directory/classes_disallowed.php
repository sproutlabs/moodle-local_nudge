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

namespace local_nudge\check\directory;

use core\check\result;

/**
 * @package     local_nudge\check\directory
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @copyright   GNU GPL v3 or later
 */
class classes_disallowed extends abstract_nudge_access_check {
    protected static string $name = 'Classes Directories';
    protected static string $badlevel = result::WARNING;
    protected static string $filepath = 'classes/local/nudge.php';
    protected static string $desc = <<<HTML
    <p>We'd recommend you disable the serving of nudge's <code>classes</code> directory.</p>
    <p>Specifically I check <code>classes/local/nudge.php</code>.</p>
    HTML;
}
