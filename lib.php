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
// phpcs:disable Squiz.PHP.CommentedOutCode.Found

/**
 * @package     local_nudge
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

use core\message\message;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;

defined('MOODLE_INTERNAL') || die();

/** @var \core_config $CFG */
global $CFG;

require_once($CFG->dirroot . '/user/profile/lib.php');

/**
 * Adds a link to manage Nudge instances for this course.
 *
 * @access public
 *
 * @param navigation_node  $parentnode
 * @param stdClass         $course
 * @param context_course   $context
 * @return void
 */
function local_nudge_extend_navigation_course(
    navigation_node $parentnode,
    stdClass $course,
    context_course $context
) {
    if (!\has_capability('local/nudge:configurenudges', $context)) {
        return;
    }

    $url = new moodle_url('/local/nudge/manage_nudges.php', [
        'courseid' => $course->id
    ]);

    $parentnode->add(
        \get_string('configurenudges', 'local_nudge'),
        $url,
        \navigation_node::TYPE_SETTING,
        null,
        null,
        new \pix_icon('i/settings', '')
    );
}

/**
 * Scaffolds an autocomplete form from class constant enums.
 *
 * @access public
 *
 * @param class-string $class Class to lookup consts on via reflection
 * @param string $filter Filter string the constant group of enums contain.
 *
 * @return array<string, string>
 */
function nudge_scaffold_select_from_constants($class, $filter): array {
    $rclass = new \ReflectionClass($class);
    $constants = $rclass->getConstants();

    // Filter for constants containing: `REMINDER_DATE`
    $constants = \array_filter($constants, function ($value, $name) use ($filter) {
        $match = (\strpos($name, $filter) !== false);
        return $match;
    }, \ARRAY_FILTER_USE_BOTH);

    // Convert constants to a sane reference to language strings.
    $constantfields = [];
    foreach ($constants as $name => $value) {
        $sanename = nudge_get_enum_string($name);
        $constantfields[$value] = $sanename;
    }

    return $constantfields;
}

/**
 * Gets a language string for an enum.
 *
 * EXAMPLE: `REMINDER_DATE_RELATIVE_ENROLLMENT` -> `reminderdaterelativeenrollment` then lookup that in the lang strings.
 *
 * @access public
 *
 * @param string $enumstring
 * @return string
 */
function nudge_get_enum_string($enumstring): string {
    $langname = \strtolower(\str_replace('_', '', $enumstring));
    return \get_string($langname, 'local_nudge');
}

/**
 * Gets a templated {@see message} for this instance of nudge.
 *
 * @access public
 *
 * @param nudge $nudge
 * @param \core\entity\user|stdClass $user
 * @param \core\entity\user|stdClass|null $manager
 * @return message
 */
function nudge_get_email_message($nudge, $user, $manager = null): message {
    /** @var \moodle_database $DB */
    global $DB;

    // Grab some context for the template.
    if ($manager === null) {
        $notification = $nudge->get_learner_notification();
    } else {
        $notification = $nudge->get_manager_notification();
    }

    $notificationcontents = $notification->get_contents($user->lang);
    $notificationcontent = array_pop($notificationcontents);
    /** @var \core\entity\user|stdClass|false */
    $userfrom = $DB->get_record('user', ['id' => $notification->userfromid]);
    $course = $nudge->get_course();

    // Passing a whole bunch of values through to avoid new queries.
    $subject = nudge_hydrate_notification_template(
        $notificationcontent->subject,
        $user,
        $course,
        $userfrom,
        $notification
    );

    $body = nudge_hydrate_notification_template(
        $notificationcontent->body,
        $user,
        $course,
        $userfrom,
        $notification
    );

    $message = new message();
    $message->component = 'local_nudge';
    $message->name = ($manager) ? 'manageremail' : 'learneremail';
    $message->userfrom = $userfrom;
    $message->userto = ($manager === null) ? $user : $manager;
    $message->subject = $subject;
    $message->fullmessageformat = \FORMAT_HTML;
    $message->fullmessagehtml = $body;
    $message->notification = 1;
    $message->courseid = $course->id;
    $message->contexturl = new moodle_url('/course/view.php', ['id' => $course->id]);
    $message->contexturlname = 'Course Link';

    $content = ['*' => [
        'header' => <<<HTML
            <h1>{$notification->title}</h1>
        HTML,
    ]];
    $message->set_additional_content('email', $content);

    return $message;
}

/**
 * Templates a notification string with the expected content.
 *
 * @access public
 *
 * {@see nudge::TEMPLATE_VARIABLES} when changing template variables.
 *
 * @param \core\entity\user|stdClass $user
 * @param \core\entity\course|stdClass $course
 * @param \core\entity\user|stdClass $userfrom
 */
function nudge_hydrate_notification_template(
    string $contenttotemplate,
    $user,
    $course,
    $userfrom,
    nudge_notification $notification
): string {
    /** @var \core_config $CFG */
    global $CFG;

    $templatevars = nudge::TEMPLATE_VARIABLES;

    $templatevars['{user_firstname}'] = $user->firstname;
    $templatevars['{user_lastname}'] = $user->lastname;
    $templatevars['{course_fullname}'] = $course->fullname;
    $templatevars['{course_shortname}'] = $course->shortname;
    $templatevars['{course_link}'] = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
    $templatevars['{sender_firstname}'] = $userfrom->firstname;
    $templatevars['{sender_lastname}'] = $userfrom->lastname;
    $templatevars['{notification_title}'] = $notification->title;

    $result = \strtr($contenttotemplate, $templatevars);

    return $result;
}

/**
 * Gets a list of managers for a user. This calls the correct function based on the custommangerresolution setting.
 *
 * @access public
 *
 * @param \core\entity\user|stdClass $user
 * @return array<\core\entity\user|stdClass>
 */
function nudge_get_managers_for_user($user): array {
    /** @var \core_config $CFG */
    global $CFG;

    $custommanagerresolutionenabled = (bool) get_config('local_nudge', 'custommangerresolution');

    if ($custommanagerresolutionenabled) {
        // First check its setup correctly.
        if (
            get_config('local_nudge', 'managermatchonfield') == null ||
            get_config('local_nudge', 'managermatchwithfield') == null
        ) {
            throw new moodle_exception(
                'cantmatchmanager',
                'local_nudge',
                $CFG->wwwroot . '/admin/settings.php?section=managelocalnudge'
            );
            return [];
        }

        return [nudge_moodle_get_manager_for_user($user)];
    } else {
        return nudge_totara_get_managers_for_user($user);
    }
}

/**
 * Return all the managers for this user.
 *
 * @access private This is public but its preferable that you use the wrapper function {@see nudge_get_managers_for_user}.
 *
 * @param \core\entity\user|stdClass $user
 * @return array<\core\entity\user|stdClass>
 */
function nudge_totara_get_managers_for_user($user): array {
    /**
     * @var array<\core\entity\user|stdClass> $allmanagers
     */
    $allmanagers = [];

    $managerrelation = \totara_core\relationship\relationship::load_by_idnumber('manager');
    $usermanagerrelations = $managerrelation->get_users(['user_id' => $user->id], context_system::instance());

    foreach ($usermanagerrelations as $managerdto) {
        $allmanagers[] = core_user::get_user($managerdto->get_user_id());
    }

    return $allmanagers;
}

/**
 * Returns the manger for this user. In our current setup in MOODLE users can only have one manager.
 *
 * @access private This is public but its preferable that you use the wrapper function {@see nudge_get_managers_for_user}.
 *
 * @param \core\entity\user|stdClass $user
 * @return \core\entity\user|stdClass|null
 */
function nudge_moodle_get_manager_for_user($user): ?stdClass {
    /** @var \moodle_database $DB */
    global $DB;

    // Load in the custom fields.
    profile_load_data($user);

    $matchwithfield = get_config('local_nudge', 'managermatchwithfield');
    $matchonfield = get_config('local_nudge', 'managermatchonfield');

    // These are always custom fields.
    $matchwith = $user->{"profile_field_{$matchwithfield}"};

    try {
        $manager = $DB->get_record(
            'user',
            [
                $matchonfield => $matchwith
            ],
            '*',
            IGNORE_MULTIPLE
        );
    } catch (dml_exception $e) {
        // TODO: Log failed to find manager.
        return null;
    }

    // Null if there is no manager.
    return $manager ?: null;
}
