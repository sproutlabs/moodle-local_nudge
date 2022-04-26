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

use core\check\check;
use core\check\result;
use curl;

/**
 * @package     local_nudge\check\directory
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @copyright   GNU GPL v3 or later
 */
abstract class abstract_nudge_access_check extends check {

    protected static string $name;
    protected static string $badlevel = result::WARNING;
    protected static string $filepath;
    protected static string $desc;

    public function get_name(): string {
        return 'Nudge - ' . static::$name . ' - access';
    }

    public function get_result(): result {
        /** @var \core_config $CFG */
        global $CFG;

        $result = new result(
            static::$badlevel,
            static::$name . ' are web accessible.',
            static::$desc
        );

        $curl = new curl();
        $curl->get($CFG->wwwroot . '/local/nudge/' . static::$filepath);
        if ($curl->get_info()['http_code'] !== 200) {
            $result = new result(
                result::OK,
                static::$name . ' are not web accessible.',
                <<<HTML
                <p>No action is required!</p>
                HTML
            );
        }

        return $result;
    }
}
