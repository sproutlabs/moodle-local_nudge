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
class tests_disallowed extends abstract_nudge_access_check {
    protected static string $name = 'Tests Directories';
    protected static string $badlevel = result::WARNING;
    protected static string $filepath = 'tests/lib_test.php';
    protected static string $desc = <<<HTML
    <p>You should take <strong>immediate</strong> to ensure that nudge's <code>tests</code> directory is
        from the web.</p>
    <p>Specifically I check <code>tests/lib_test.php</code>.</p>
    HTML;
}
