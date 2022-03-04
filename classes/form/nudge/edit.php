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
 * @package     local_nudge\form\nudge
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\form\nudge;

require_once(__DIR__ . '/../../../lib.php');

use coding_exception;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;
use moodleform;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_nudge\form\nudge
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */
class edit extends moodleform
{
    /**
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition()
    {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', \PARAM_INT);
        
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', \PARAM_INT);
        
        $mform->addElement('checkbox', 'isenabled', \get_string('isenabled', 'local_nudge'));
        $mform->addHelpButton('isenabled', 'isenabled', 'local_nudge');

        $reminder_recipient_enum = scaffold_select_from_constants(nudge::class, 'REMINDER_RECIPIENT');
        $mform->addElement(
            'select',
            'reminderrecipient',
            \get_string('reminderrecipient', 'local_nudge'),
            $reminder_recipient_enum,
        );
        $mform->addHelpButton('reminderrecipient', 'reminderrecipient', 'local_nudge');

        $notification_array = $this->get_notification_options();

        // Show unless the value is only managers.
        $mform->addElement(
            'autocomplete',
            'linkedlearnernotificationid',
            'Notification for the Learner',
            $notification_array
        );
        $mform->hideIf('linkedlearnernotificationid', 'reminderrecipient', 'in', [nudge::REMINDER_RECIPIENT_MANAGERS]);

        // Show unless the value is only learners.
        $mform->addElement(
            'autocomplete',
            'linkedmanagernotificationid',
            'Notification for the Managers',
            $notification_array
        );
        $mform->hideIf('linkedmanagernotificationid', 'reminderrecipient', 'in', [nudge::REMINDER_RECIPIENT_LEARNER]);

        $reminder_type_enum = scaffold_select_from_constants(nudge::class, 'REMINDER_DATE');
        $mform->addElement(
            'select',
            'remindertype',
            \get_string('remindertype', 'local_nudge'),
            $reminder_type_enum,
        );
        $mform->addHelpButton('remindertype', 'remindertype', 'local_nudge');

        // Show if the reminder type is fixed.
        $mform->addElement(
            'date_selector',
            'remindertypefixeddate',
            \get_string('remindertypefixeddate', 'local_nudge'),
            [
                // TODO dynamic startyear.
                'startyear' => 2022,
                'optional' => false
            ]
        );
        $mform->hideIf('remindertypefixeddate', 'remindertype', 'neq', nudge::REMINDER_DATE_INPUT_FIXED);
        
        $mform->addElement(
            'duration',
            'reminderdaterelativeenrollment',
            'Repeat every',
            [
                // Default to days
                'defaultunit' => 86400
            ]
        );
        $mform->setDefault('reminderdaterelativeenrollment', 86400);
        $mform->hideIf('reminderdaterelativeenrollment', 'remindertype', 'neq', nudge::REMINDER_DATE_RELATIVE_ENROLLMENT);

        $mform->addElement(
            'duration',
            'reminderdaterelativecourseend',
            'Reminder x before course ends',
            [
                // Default to days
                'defaultunit' => 86400
            ]
        );
        $mform->hideIf('reminderdaterelativecourseend', 'remindertype', 'neq', nudge::REMINDER_DATE_RELATIVE_COURSE_END);

        $this->add_action_buttons();
    }

    /**
     * {@inheritDoc}
     * @return nudge|null
     */
    public function get_data()
    {
        $data = parent::get_data();
        if ($data === null) return null;

        $instance_data = [
            'id' => $data->id,
            'courseid' => $data->courseid,
            'linkedlearnernotificationid' => (isset($data->linkedlearnernotificationid)) ? $data->linkedlearnernotificationid : 0,
            'linkedmanagernotificationid' => (isset($data->linkedmanagernotificationid)) ? $data->linkedmanagernotificationid : 0,
            // TODO BUG this doesn't work on initial creation eg. new course -> adjust nudge -> initially save as enabled.
            'isenabled' => (isset($data->isenabled)) ? true : false,
            'reminderrecipient' => $data->reminderrecipient,
            'remindertype' => $data->remindertype
        ];

        switch ($data->remindertype) {
            case(nudge::REMINDER_DATE_INPUT_FIXED):
                $instance_data['remindertypefixeddate'] = $data->remindertypefixeddate;
                break;
            case(nudge::REMINDER_DATE_RELATIVE_ENROLLMENT):
                $instance_data['remindertypeperiod'] = $data->reminderdaterelativeenrollment;
                break;
            case(nudge::REMINDER_DATE_RELATIVE_COURSE_END):
                $instance_data['remindertypeperiod'] = $data->reminderdaterelativecourseend;
                break;
            default:
                // UNREACHABLE!()
                \print_error('invaliddata');
        }

        return new nudge($instance_data);
    }

    /**
     * {@inheritDoc}
     * @param nudge $nudge
     * @return void
     */
    public function set_data($nudge)
    {
        if (!$nudge instanceof nudge) {
            throw new coding_exception(\sprintf('You must provide a instance of %s to this form %s.', nudge::class, __CLASS__));
        }

        $this->_form->setDefaults([
            'id' => $nudge->id,
            'courseid' => $nudge->courseid,
            'linkedmanagernotificationid' => $nudge->linkedlearnernotificationid,
            'linkedmanagernotificationid' => $nudge->linkedmanagernotificationid,
            'isenabled' => $nudge->isenabled,
            'reminderrecipient' => $nudge->reminderrecipient,
            'remindertype' => $nudge->remindertype,
            'remindertypefixeddate' => $nudge->remindertypefixeddate,
            'reminderdaterelativeenrollment' => $nudge->remindertypeperiod,
            'reminderdaterelativecourseend' => $nudge->remindertypeperiod
        ]);
    }

    /**
     * Gets an array of available {@see nudge_notification}s to choose from with a select.
     * 
     * @return array<string, string>
     */
    private function get_notification_options()
    {
        $notifications = nudge_notification_db::get_all();
        return \array_merge(
            // TODO: Default will come from lang strings.
            ['0' => 'Default'],
            \array_combine(
                \array_column($notifications, 'id'),
                \array_column($notifications, 'title')
            )
        );
    }
}
