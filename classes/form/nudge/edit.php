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

/**
 * @package     local_nudge\form\nudge
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\form\nudge;

use coding_exception;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge;
use moodle_exception;
use moodleform;

use function get_string;

defined('MOODLE_INTERNAL') || die();

/** @var \core_config $CFG */
global $CFG;
require_once("$CFG->libdir/formslib.php");
require_once(__DIR__ . '/../../../lib.php');

/**
 * @package     local_nudge\form\nudge
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */
class edit extends moodleform {

    /**
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', \PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', \PARAM_INT);

        $headergroup = [
            $mform->createElement('text', 'title', get_string('form_nudge_title')),
            $mform->createElement('checkbox', 'isenabled', get_string('form_nudge_isenabled', 'local_nudge'))
        ];
        $mform->addGroup($headergroup, 'group_header', 'Title');
        $mform->addGroupRule('group_header', [
            'title' => [
                [
                    get_string('validation_nudge_needtitle', 'local_nudge'),
                    'required'
                ]
            ]
        ]);

        $mform->addHelpButton('group_header', 'form_nudge_isenabled', 'local_nudge');

        $reminderrecipientenum = nudge_scaffold_select_from_constants(nudge::class, 'REMINDER_RECIPIENT');
        $mform->addElement(
            'select',
            'reminderrecipient',
            get_string('form_nudge_reminderrecipient', 'local_nudge'),
            $reminderrecipientenum,
        );
        $mform->addHelpButton('reminderrecipient', 'form_nudge_reminderrecipient', 'local_nudge');

        $notificationarray = $this->get_notification_options();

        // Show unless the value is only managers.
        $mform->addElement(
            'autocomplete',
            'linkedlearnernotificationid',
            get_string('form_nudge_learnernotification', 'local_nudge'),
            $notificationarray
        );
        $mform->hideIf('linkedlearnernotificationid', 'reminderrecipient', 'in', [nudge::REMINDER_RECIPIENT_MANAGERS]);
        $mform->addHelpButton('linkedlearnernotificationid', 'form_nudge_learnernotification', 'local_nudge');

        // Show unless the value is only learners.
        $mform->addElement(
            'autocomplete',
            'linkedmanagernotificationid',
            get_string('form_nudge_managernotification', 'local_nudge'),
            $notificationarray
        );
        $mform->hideIf('linkedmanagernotificationid', 'reminderrecipient', 'in', [nudge::REMINDER_RECIPIENT_LEARNER]);
        $mform->addHelpButton('linkedmanagernotificationid', 'form_nudge_managernotification', 'local_nudge');

        $remindertypeenum = nudge_scaffold_select_from_constants(nudge::class, 'REMINDER_DATE');
        $mform->addElement(
            'select',
            'remindertype',
            get_string('form_nudge_remindertype', 'local_nudge'),
            $remindertypeenum,
        );
        $mform->addHelpButton('remindertype', 'form_nudge_remindertype', 'local_nudge');

        // Show if the reminder type is fixed.
        $mform->addElement(
            'date_selector',
            'remindertypefixeddate',
            get_string('form_nudge_remindertypefixeddate', 'local_nudge'),
            [
                'startyear' => get_config('local_nudge', 'uxstartdate'),
                'optional' => false
            ]
        );
        $mform->hideIf('remindertypefixeddate', 'remindertype', 'neq', nudge::REMINDER_DATE_INPUT_FIXED);
        $mform->addHelpButton('remindertypefixeddate', 'form_nudge_remindertypefixeddate', 'local_nudge');

        $mform->addElement(
            'duration',
            'reminderdaterelativeenrollment',
            get_string('form_nudge_remindertyperelativedate', 'local_nudge'),
            [
                // Default to days.
                'defaultunit' => \DAYSECS
            ]
        );
        $mform->setDefault('reminderdaterelativeenrollment', 86400);
        $mform->hideIf('reminderdaterelativeenrollment', 'remindertype', 'neq', nudge::REMINDER_DATE_RELATIVE_ENROLLMENT);
        $mform->addHelpButton('reminderdaterelativeenrollment', 'form_nudge_remindertyperelativedate', 'local_nudge');

        $mform->addElement(
            'duration',
            'reminderdaterelativecourseend',
            get_string('form_nudge_reminderdatecoruseend', 'local_nudge'),
            [
                // Default to days.
                'defaultunit' => \DAYSECS
            ]
        );
        $mform->hideIf('reminderdaterelativecourseend', 'remindertype', 'neq', nudge::REMINDER_DATE_RELATIVE_COURSE_END);
        $mform->addHelpButton('reminderdaterelativecourseend', 'form_nudge_reminderdatecoruseend', 'local_nudge');

        $this->add_action_buttons();
    }

    /**
     * {@inheritDoc}
     * @return nudge|null
     */
    public function get_data() {
        $data = parent::get_data();

        if ($data == null) {
            return null;
        }

        $instancedata = [
            'id' => $data->id,
            'courseid' => $data->courseid,
            'linkedlearnernotificationid' => (isset($data->linkedlearnernotificationid)) ? $data->linkedlearnernotificationid : 0,
            'linkedmanagernotificationid' => (isset($data->linkedmanagernotificationid)) ? $data->linkedmanagernotificationid : 0,
            'title' => (isset($data->group_header['title']))
                ? $data->group_header['title']
                : nudge::DEFAULTS['title'],
            'isenabled' => (isset($data->group_header->isenabled)) ? true : false,
            'reminderrecipient' => $data->reminderrecipient,
            'remindertype' => $data->remindertype
        ];

        switch ($data->remindertype) {
            case (nudge::REMINDER_DATE_INPUT_FIXED):
                $instancedata['remindertypefixeddate'] = $data->remindertypefixeddate;
                break;
            case (nudge::REMINDER_DATE_RELATIVE_ENROLLMENT):
                $instancedata['remindertypeperiod'] = $data->reminderdaterelativeenrollment;
                break;
            case (nudge::REMINDER_DATE_RELATIVE_COURSE_END):
                $instancedata['remindertypeperiod'] = $data->reminderdaterelativecourseend;
                break;
            default:
                // UNREACHABLE!.
                throw new moodle_exception(
                    'expectedunreachable',
                    'local_nudge'
                );
        }

        return new nudge($instancedata);
    }

    /**
     * {@inheritDoc}
     * @param nudge $nudge
     * @return void
     */
    public function set_data($nudge) {
        if (!$nudge instanceof nudge) {
            throw new coding_exception(\sprintf('You must provide a instance of %s to this form %s.', nudge::class, __CLASS__));
        }

        // TODO This is a bugged.
        $reltime = ($nudge->remindertypeperiod === null || $nudge->remindertypeperiod === 0)
            ? \DAYSECS
            : $nudge->remindertypeperiod;

        $defaults = [
            'id' => $nudge->id,
            'courseid' => $nudge->courseid,
            'linkedlearnernotificationid' => $nudge->linkedlearnernotificationid,
            'linkedmanagernotificationid' => $nudge->linkedmanagernotificationid,
            'group_header[title]' => $nudge->title,
            'group_header[isenabled]' => $nudge->isenabled,
            'reminderrecipient' => $nudge->reminderrecipient,
            'remindertype' => $nudge->remindertype,
            'remindertypefixeddate' => $nudge->remindertypefixeddate,
            'reminderdaterelativeenrollment' => $reltime,
            'reminderdaterelativecourseend' => $reltime,
            'createdby' => get_string('form_noyetset', 'local_nudge'),
            'timecreated' => get_string('form_noyetset', 'local_nudge'),
            'lastmodifiedby' => get_string('form_noyetset', 'local_nudge'),
            'lastmodified' => get_string('form_noyetset', 'local_nudge'),
        ];

        $this->_form->setDefaults($defaults);
    }

    /**
     * Validates this form on the serverside for more complex valdiation than `->addRule()`.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $files
     * @return array<string, string>
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate it: Has notifications linked for the selected REMINDER_RECIPIENT type.
        if (!empty($data['reminderrecipient'])) {
            switch ($data['reminderrecipient']) {
                case(nudge::REMINDER_RECIPIENT_BOTH):
                    if (empty($data['linkedlearnernotificationid'])) {
                        $errors['linkedlearnernotificationid'] = get_string(
                            'validation_nudge_neednotifications',
                            'local_nudge',
                            \nudge_get_enum_string('REMINDER_RECIPIENT_BOTH')
                        );
                    }
                    if (empty($data['linkedmanagernotificationid'])) {
                        $errors['linkedmanagernotificationid'] = get_string(
                            'validation_nudge_neednotifications',
                            'local_nudge',
                            \nudge_get_enum_string('REMINDER_RECIPIENT_MANAGERS')
                        );
                    }
                    break;
                case(nudge::REMINDER_RECIPIENT_LEARNER):
                    if (empty($data['linkedlearnernotificationid'])) {
                        $errors['linkedlearnernotificationid'] = get_string(
                            'validation_nudge_neednotifications',
                            'local_nudge',
                            \nudge_get_enum_string('REMINDER_RECIPIENT_LEARNER')
                        );
                    }
                    break;
                case(nudge::REMINDER_RECIPIENT_MANAGERS):
                    if (empty($data['linkedmanagernotificationid'])) {
                        $errors['linkedmanagernotificationid'] = get_string(
                            'validation_nudge_neednotifications',
                            'local_nudge',
                            \nudge_get_enum_string('REMINDER_RECIPIENT_MANAGERS')
                        );
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * Gets an array of available {@see \local_nudge\local\nudge_notification}s to choose from with a select.
     *
     * @return array<string, string>
     */
    private function get_notification_options() {
        $notifications = nudge_notification_db::get_all();
        return \array_combine(
            \array_column($notifications, 'id'),
            \array_column($notifications, 'title')
        );
    }
}
