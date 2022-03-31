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

// phpcs:disable moodle.Commenting.InlineComment.DocBlock

/**
 * @package     local_nudge
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @var NudgeMessageProviders See PHPStan neon file for definition.
*/
$messageproviders = [
    'learneremail' => [
        'capability' => 'local/nudge:receivelearneremail',
        'defaults' => [
            'email' => \MESSAGE_FORCED + \MESSAGE_DEFAULT_LOGGEDOFF + \MESSAGE_DEFAULT_LOGGEDIN,
            'popup' => \MESSAGE_DISALLOWED,
        ]
    ],
    'manageremail' => [
        'capability' => 'local/nudge:receivemanageremail',
        'defaults' => [
            'email' => \MESSAGE_FORCED + \MESSAGE_DEFAULT_LOGGEDOFF + \MESSAGE_DEFAULT_LOGGEDIN,
            'popup' => \MESSAGE_DISALLOWED,
        ]
    ]
];
