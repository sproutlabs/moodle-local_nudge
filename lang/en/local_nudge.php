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

defined('MOODLE_INTERNAL') || die();

// META
$string['pluginname']                                               =       'Nudge';
$string['crontask']                                                 =       'Nudge Cron Task';
$string['configurenudges']                                          =       'Configure Nudge Reminders';
$string['configurenudgenotifications']                              =       'Configure Site Nudge Notifications';


// ---------------------------------------
//              DISPLAY
// ---------------------------------------
// This is here in case you wanted to clarify and prefix with `Edit: {$a}` (a is title) etc.
$string['notification_edit_link']                                   =       '{$a}';
$string['nudge_edit_link']                                          =       '{$a}';
$string['nudge_status_text_enabled']                                =       'Enabled';
$string['nudge_status_text_disabled']                               =       'Disabled';


// ---------------------------------------
//             MANAGE PAGES
// ---------------------------------------
// Nudge
$string['manage_nudge_add']                                         =       'Add a Nudge';
$string['manage_nudge_notificationslink']                           =       'Manage Notifications';
$string['manage_nudge_col_title']                                   =       'Title';
$string['manage_nudge_col_status']                                  =       'Status';
$string['manage_nudge_col_learnerreminder']                         =       'Learner Reminder';
$string['manage_nudge_col_managerreminder']                         =       'Manager Reminder';
$string['manage_nudge_col_type']                                    =       'Type';
$string['manage_nudge_col_actions']                                 =       'Actions';

// Nudge Notification
$string['manage_notification_add']                                  =       'Add a Nudge Notification';
$string['manage_notification_col_title']                            =       'Title';
$string['manage_notification_col_count']                            =       'Linked Translation Count';
$string['manage_notification_col_nudge_count']                      =       'Linked Nudges';
$string['manage_notification_col_actions']                          =       'Actions';


// ---------------------------------------
//              EDIT FORMS
// ---------------------------------------
// Universal
$string['form_metahdr']                                             =       'Metadata';
$string['form_noyetset']                                            =       'Not yet set.';

// Nudge
$string['form_nudge_title']                                         =       'Title';

$string['form_nudge_isenabled']                                     =       'Is Enabled?';
$string['form_nudge_isenabled_help']                                =       <<<HTML
<p>You can enable or disable this notification here.</p>
<p>A notification may disable itself once certain conditions have been met:</p>
<ol>
    <li>Enrollment date: When the course has ended.</li>
    <li>Course end date: When the notification prior to course end has been sent.</li>
    <li>Fixed date: When the notification has been sent.</li>
</ol>
HTML;

$string['form_nudge_reminderrecipient']                             =       'Reminder Recipient';
$string['form_nudge_reminderrecipient_help']                        =       <<<EOF
Select recipients of this nudge reminder.
EOF;

$string['form_nudge_learnernotification']                           =       'Notification for the Learner';
$string['form_nudge_learnernotification_help']                      =       <<<EOF
You can select a notification from the Site's Nudge Notifications to be sent to the Learner when the nudge triggers.
EOF;

$string['form_nudge_managernotification']                           =       'Notification for the Managers';
$string['form_nudge_managernotification_help']                      =       <<<EOF
You can select a notification from the Site's Nudge Notifications to be sent to the Learner's Managers when the nudge triggers.
EOF;

$string['form_nudge_remindertype']                                  =       'Reminder Timing';
$string['form_nudge_remindertype_help']                             =       <<<EOF
Select a timing method to base sent nudges on.
EOF;

$string['form_nudge_remindertypefixeddate']                         =       'Choose a fixed reminder date';
$string['form_nudge_remindertypefixeddate_help']                    =       <<<EOF
Pick a fixed date for this Nudge to trigger on, The Nudge will then disable itself.
EOF;

$string['form_nudge_remindertyperelativedate']                      =       'Remind x after the Learner\'s enrollment';
$string['form_nudge_remindertyperelativedate_help']                 =       <<<EOF
You can setup this Nudge to remind Learners relative to their enrollment date. The Nudge will then disable itself.
EOF;

$string['form_nudge_remindertyperelativedaterecurring']             =       'Repeat every x after a Learner\'s enrollment';
$string['form_nudge_remindertyperelativedaterecurring_help']        =       <<<EOF
NOTE: This is highly experimental. You can configure a Nudge to remind every x period following their enrolment until they complete this course.
This will end with the course's enddate or a learner's completion.
EOF;

$string['form_nudge_reminderdatecourseend']                         =       'Reminder x before course ends';
$string['form_nudge_reminderdatecourseend_help']                    =       <<<EOF
You can configure a Nudge to reminder on a given period prior to course end.
EOF;

$string['form_nudge_deleteconfirm']                                 =       'Are you sure you want delete this nudge?';

// Notification
$string['form_notification_title']                                  =       'Add a title';

$string['form_notification_userfrom']                               =       'Select a user as the sender for this email';

$string['form_notification_templatevar_title']                      =       'Template Infomation';
$string['form_notification_templatevar_help']                       =       'You can use the following properties in a translation:';

$string['form_notification_translation_header']                     =       'Unsaved Translation';
$string['form_notification_translation_template']                   =       'Translation - {$a->language}: {$a->subject}';

$string['form_notification_selectlang']                             =       'Select a language';
$string['form_notification_addsubject']                             =       'Add a subject';
$string['form_notification_addbody']                                =       'Add a body';

$string['form_notification_addprompt']                              =       'Add {no} more translation{possible_s}';

$string['form_notification_deleteconfirm']                          =       'Are you sure you want delete this notification?';


// ---------------------------------------
//              ENUM TITLES
// See: local/nudge/lib.php::nudge_scaffold_select_from_constants()
// ---------------------------------------
// Reminder Date
$string['reminderdateinputfixed']                                   =       'Reminder Date input fixed';
$string['reminderdaterelativeenrollment']                           =       'Reminder Date relative enrollment';
$string['reminderdaterelativeenrollmentrecurring']                  =       'Reminder Date relative enrollment recurring';
$string['reminderdaterelativecourseend']                            =       'Reminder Date relative Course end';
$string['reminderdatecoursecompletion']                             =       'Remind on Course completion';
$string['reminderdateuserenrolment']                                =       'Remind on User enrolment';

// Reminder Recipient
$string['reminderrecipientlearner']                                 =       'The Learner';
$string['reminderrecipientmanagers']                                =       'The Learner\'s Managers';
$string['reminderrecipientboth']                                    =       'Both the Learner and their Managers';


// ---------------------------------------
//                  ADMIN
// ---------------------------------------
// General
$string['configurenudge']                                           =       'Configure Nudge';
$string['manage_settings']                                          =       'Configure Nudge Settings';

// Manager resolution settings.
$string['admin_manager_heading']                                    =       'Manager resolution Settings';
$string['admin_manager_heading_desc']                               =       <<<EOF
These are just for emulation on MOODLE, Totara already has a system for this.
EOF;

$string['admin_manager_managerresolution']                          =       'Custom manager resolution enabled';
$string['admin_manager_managerresolution_desc']                     =       <<<EOF
If this is enabled the below two fields will be used for custom manager resolution.
In Totara this is generally not a good idea however this is <em><strong>needed</strong> for MOODLE solutions</em>.
EOF;

$string['admin_manager_matchwith_field']                            =       'Manager match with field';
$string['admin_manager_matchwith_field_desc']                       =       <<<EOF
This field will be used to match managers with.
This will be the field on a Learner's profile that matches the match on field on the Manager's profile
EOF;

$string['admin_manager_matchon_field']                              =       'Manager match on field';
$string['admin_manager_matchon_field_desc']                         =       <<<EOF
This field will be used to match managers on.
This is the "unique" identifier for a manager
EOF;

// Language resolution settings.
$string['admin_language_heading']                                   =       'Language resolution settings';
$string['admin_language_heading_desc']                              =       <<<'HTML'
<div class="alert alert-info p-3 pt-4 mt-4">
    <p>By default this plugin uses the user's language preference in MOODLE however in some cases you may wish
        to base the user's email message language on a custom profile field.</p>
    <p>It is <strong>important to note</strong> that a custom field must exist before you can select it here.</p>
</div>
HTML;

$string['admin_language_languageresolution']                        =       'Custom language resolution enabled';
$string['admin_language_languageresolution_desc']                   =       <<<EOF
If this is enabled language preference will be based on the below selected custom field rather than
the inbuilt moodle system.
EOF;

$string['admin_language_field']                                     =       'Custom language field';
$string['admin_language_field_desc']                                =       <<<'HTML'
<p>This is the field that will be used as the user's preference language.</p>
<p>You <strong>must ensure</strong> that this field is considered a valid MOODLE/Totara language code.</p>
<center>See <a href="https://download.moodle.org/langpack/4.0/">here</a> for MOODLE language codes (the prefix before the zip file)
    and <a href="https://download.totaralms.com/lang/T16/">here</a> for Totara ones.</center>
HTML;

// UX Settings
$string['admin_ux_heading']                                         =       'User Experience Settings';
$string['admin_ux_heading_desc']                                    =       <<<EOF
You can configure some settings to make Nudge easier to work with here.
EOF;

$string['admin_ux_addtranslationcount']                             =       'Notification add count';
$string['admin_ux_addtranslationcount_desc']                        =       <<<EOF
The amount of translations to add each time when creating a Nudge Notification.
EOF;

$string['admin_ux_enddate']                                         =       'End date';
$string['admin_ux_enddate_desc']                                    =       <<<EOF
You can select a end date here to limit date pickers to something reasonable.
EOF;

// Performance Settings
$string['admin_performance_heading']                                =       'Performance Settings';
$string['admin_performance_heading_desc']                           =       <<<EOF
You can make some performance tradeoffs here.
EOF;

// TODO: Implement me
$string['admin_performance_nolog']                                  =       'Performance mode';
$string['admin_performance_nolog_desc']                             =       <<<EOF
Enabling this will disable the creation of events, reducing queries required on the cron worker.
However this is usually a bad idea since it makes it almost impossible to track changes.
EOF;


// ---------------------------------------
//                 ERRORS
// ---------------------------------------
$string['expectedunreachable']                                      =       'Expected unreachable, It\'s possible a malformed database value was encountered.';
$string['nudgenotificationdoesntexist']                             =       'Can\'t find Nudge Notification with the ID: {$a}';
$string['nudgedoesntexist']                                         =       'Can\'t find Nudge with the ID: {$a}';
$string['cantmatchmanager']                                         =       'The option to match on manager custom fields is on but no field is selected';


// ---------------------------------------
//               VALIDATION
// ---------------------------------------
// Nudge
$string['validation_nudge_neednotifications']                       =       'The selected recipient type was: "{$a}" but there wasn\'t wasn\'t enough notifications to cover the recipients.';
$string['validation_nudge_needtitle']                               =       'You need to supply a title for identification';
$string['validation_nudge_timepastcourseend']                       =       'The reminder will occur past the courses end date: "{$a}". Please update the value to something that will occur prior';
$string['validation_nudge_needfixeddate']                           =       'You must supply a fixed date for this type of reminder';

// Nudge Notification
$string['validation_notification_needsender']                       =       'You must set a sender';
$string['validation_notification_needtitle']                        =       'Title is required for usability';
$string['validation_notification_duplicatelangs']                   =       'Ensure there is only one translation for each language';


// ---------------------------------------
//               MESSAGES
// ---------------------------------------
$string['messageprovider:learneremail']                             =       'Nudge message to learners';
$string['messageprovider:manageremail']                             =       'Nudge message to a learner\'s managers';
$string['messageprovider:owneremail']                               =       'Nudge message to the last editor and creator';


// ---------------------------------------
//               EXCEPTIONS
// ---------------------------------------
$string['languagenotsupported']                                     =       <<<'HTML'
<p>Your language is not supported, The primary translation has been offered for now.</p>
<p>Please contact a Site Administrator and notify them that a translation for your
    language: {$a->langcode} ({$a->language}) is not offered.</p>
HTML;
$string['cantmanagesitenudges']                                     =       'You can\'t attach nudge reminders to the site course.';
$string['nudge_exception_unlinked_notification_subject']            =       '{$a} - Exception for a nudge you manage';
$string['nudge_exception_unlinked_notification_body']               =       <<<'HTML'
<h5>Hi There,</h5>
<p>This notification comes from <code>{$a->sitefullname}</code></p>
<p>You are receiving this notification because a nudge instance you manage has been disabled.</p>
<p>By manage it could mean either you were the last person to edit or the person who created:
    <code>{$a->nudgetitle}</code>.</p>
<br />
<p>The notification with the title <code>{$a->notificationtitle}</code> has been deleted.</p>
<p>The nudge instance with the title <code>{$a->nudgetitle}</code> has been disabled because of this.</p>
<p>It is <strong>crucial</strong> that you logon and setup a new notification if you wish for notifications to continue
    sending</p>
<p><em><strong>NOTE:</strong> if you receive two of these messages for the same nudge its likely that the notification
        was in use
        as both the learner and manager notification.</em></p>
HTML;
