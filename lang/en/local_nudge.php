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
// phpcs:disable Squiz.WhiteSpace

/**
 * @package     local_nudge
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

// META
$string['pluginname']                               =       'Nudge';
$string['crontask']                                 =       'Nudge Cron Task';
$string['trackcourse']                              =       'Adjust Nudge Reminders';
$string['managetracking']                           =       'Manage Nudge Reminders';
$string['configurenudgenotifications']              =       'Configure Site Nudge Notifications';


// ---------------------------------------
//             MANAGE PAGES
// ---------------------------------------
// Nudge
$string['manage_nudge_add']                         =       'Add a Nudge';

// Nudge Notification
$string['manage_notification_add']                  =       'Add a Nudge Notification';
// ---------------------------------------
//              EDIT FORMS
// ---------------------------------------
// Nudge
$string['form_nudge_isenabled']                     =       'Is Enabled?';
$string['form_nudge_isenabled_help']                =       <<<EOF
<p>You can enable or disable this notification here.</p>
<p>A notification may disable itself once certain conditions have been met:</p>
<ol>
    <li>Relative date: When the course has ended.</li>
    <li>Course end date: When the notification prior to course end has been sent.</li>
    <li>Fixed date: When the notification has been sent.</li>
</ol>
EOF;

$string['form_nudge_reminderrecipient']             =       'Reminder Recipient';
$string['form_nudge_reminderrecipient_help']        =       <<<EOF
Select recipients of this nudge reminder.
EOF;

$string['form_nudge_learnernotification']           =       'Notification for the Learner';
$string['form_nudge_learnernotification_help']      =       <<<EOF
TODO
EOF;

$string['form_nudge_managernotification']           =       'Notification for the Managers';
$string['form_nudge_managernotification_help']      =       <<<EOF
TODO
EOF;

$string['form_nudge_remindertype']                  =       'Reminder Timing';
$string['form_nudge_remindertype_help']             =       <<<EOF
Select a timing method to base sent nudges on.
EOF;

$string['form_nudge_remindertypefixeddate']         =       'Choose a fixed reminder date';
$string['form_nudge_remindertypefixeddate_help']    =       <<<EOF
TODO
EOF;

$string['form_nudge_remindertyperelativedate']      =       'Repeat every x after a user\'s enrollment';
$string['form_nudge_remindertyperelativedate_help'] =       <<<EOF
TODO make this minium 5 minutes to make it align with cron
EOF;

$string['form_nudge_reminderdatecoruseend']         =       'Reminder x before course ends';
$string['form_nudge_reminderdatecoruseend_help']    =       <<<EOF
TODO
EOF;

// Notification
$string['form_notification_title']                  =       'Add a title';

$string['form_notification_userfrom']               =       'Select a user as the sender for this email';

$string['form_notification_templatevar_title']      =       'Template Infomation';
$string['form_notification_templatevar_help']       =       'You can use the following properties in a translation:';

$string['form_notification_translation_header']     =       'Unsaved Translation';
$string['form_notification_translation_template']   =       'Translation - {$a->language}: {$a->subject}';

$string['form_notification_selectlang']             =       'Select a language';
$string['form_notification_addsubject']             =       'Add a subject';
$string['form_notification_addbody']                =       'Add a body';

$string['form_notification_addprompt']              =       'Add {no} more translation{possible_s}';

// ---------------------------------------
//              ENUM TITLES
// See: local/nudge/lib.php::nudge_scaffold_select_from_constants()
// ---------------------------------------
// Reminder Date
$string['reminderdateinputfixed']                   =       'Reminder Date Input Fixed';
$string['reminderdaterelativeenrollment']           =       'Reminder Date Relative Enrollment';
$string['reminderdaterelativecourseend']            =       'Reminder Date Relative Course End';

// Reminder Recipient
$string['reminderrecipientlearner']                 =       'The Learner';
$string['reminderrecipientmanagers']                =       'The Learner\'s Managers';
$string['reminderrecipientboth']                    =       'Both the Learner and their Managers';

// ---------------------------------------
//                  ADMIN
// ---------------------------------------
// General
$string['configurenudge']                           =       'Configure Nudge';
$string['manage_settings']                          =       'Configure Nudge Settings';

// Managers settings
$string['admin_manager_heading']                    =       'Manager Settings';
$string['admin_manager_heading_desc']               =       <<<EOF
These are just for emulation on MOODLE, Totara already has a system for this.
EOF;

$string['admin_custom_managerresolution']           =       'Custom manager resolution enabled';
$string['admin_custom_managerresolution_desc']      =       <<<EOF
If this is enabled the below two fields will be used for custom manager resolution.
In Totara this is generally not a good idea however this is <em><strong>needed</strong> for MOODLE solutions</em>.
EOF;

$string['admin_manager_matchwith_field']            =       'Manager match with field';
$string['admin_manager_matchwith_field_desc']       =       <<<EOF
This field will be used to match managers with.
This will be the field on a user's profile that matches the match on field on the Manager's profile
EOF;

$string['admin_manager_matchon_field']              =       'Manager match on field';
$string['admin_manager_matchon_field_desc']         =       <<<EOF
This field will be used to match managers on.
This is the "unique" identifier for a manager
EOF;

// UX Settings
$string['admin_ux_heading']                         =       'User Experience Settings';
$string['admin_ux_heading_desc']                    =       <<<EOF
You can configure some settings to make Nudge easier to work with here.
EOF;

$string['admin_ux_addtranslationcount']             =       'Notification add count';
$string['admin_ux_addtranslationcount_desc']        =       <<<EOF
The amount of translations to add each time when creating a Nudge Notification.
EOF;

$string['admin_ux_startdate']                       =       'Start date';
$string['admin_ux_startdate_desc']                  =       <<<EOF
You can select a start date here to limit date pickers.
EOF;

// Performance Settings
$string['admin_performance_heading']                =       'Performance Settings';
$string['admin_performance_heading_desc']           =       <<<EOF
You can make some preformance tradeoffs here.
EOF;

// TODO: Implement me
$string['admin_performance_nolog']                  =       'Performance mode';
$string['admin_performance_nolog_desc']             =       <<<EOF
Enabling this will disable the creation of events, reducing queries required on the cron worker.
However this is usually a bad idea since it makes it almost impossible to track changes.
EOF;

// TODO: convert some of the DML called from the cron to SQL.

// ---------------------------------------
//                 ERRORS
// ---------------------------------------

$string['expectedunreachable']                      =       'Expected unreachable, It\'s possible a malformed database value was encountered.';
$string['nudgenotificationdoesntexist']             =       'Can\'t find Nudge Notification with the ID: {$a}';
$string['nudgedoesntexist']                         =       'Can\'t find Nudge with the ID: {$a}';
$string['cantmatchmanager']                         =       'The option to match on manager custom fields is on but no field is selected';

// ---------------------------------------
//               VALIDATION
// ---------------------------------------

// Nudge
$string['validation_nudge_neednotifications']       =       'The selected recipient type was: "{$a}" but there wasn\'t wasn\'t enough notifications to cover the recipients.';

// Nudge Notification
$string['validation_notification_needsender']       =       'You must set a sender';
$string['validation_notification_needtitle']        =       'Title is required for usability';
