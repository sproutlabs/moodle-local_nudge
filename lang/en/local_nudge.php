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

//  META
$string['pluginname']                               =       'Nudge';
$string['crontask']                                 =       'Nudge Cron';
$string['trackcourse']                              =       'Adjust Nudging Completions';
$string['managetracking']                           =       'Manage Nudge tracking and reminders';

// NUDGE EDIT FORM
$string['isenabled']                                =       'Is Enabled?';
$string['remindertype']                             =       'Reminder Timing';
$string['reminderdateinputfixed']                   =       'Reminder Date Input Fixed';
$string['reminderdaterelativeenrollment']           =       'Reminder Date Relative Enrollment';
$string['reminderdaterelativecourseend']            =       'Reminder Date Relative Course End';
$string['reminderrecipient']                        =       'Reminder Recipient';
$string['reminderrecipientlearner']                 =       'The Learner';
$string['reminderrecipientmanagers']                =       'The Learner\'s Managers';
$string['reminderrecipientboth']                    =       'Both the Learner and their Managers';
$string['remindertypefixeddate']                    =       'Choose a fixed reminder date';
$string['remindertypeperiod']                       =       'Choose a period reminder date';

// EDIT FORM HELP
$string['isenabled_help'] = <<<EOF
You can toggle nudge reminders for this course here.

Simply enable the checkbox and save this form for more options.
EOF;
$string['remindertype_help'] = <<<EOF
Select a timing method to base sent nudges on.
EOF;
$string['reminderrecipient_help'] = <<<EOF
Select recipients of this nudge reminder.
EOF;

// NOTIFICATION FORM
$string['configurenudgenotifications']              =       'Configure nudge notifications';
$string['deletenudgenotificationconfirm']           =       'Are you sure you want to delete the Nudge notification: <strong>"{$a}"</strong>:';

// NOTIFICATION CONTENT FORM
$string['configurenudgenotificationcontents']       =       'Configure nudge notification contents';
$string['deletenudgenotificationcontentconfirm']    =       'Are you sure you want to delete the Nudge notification contents: <strong>"{$a}"</strong>:';
