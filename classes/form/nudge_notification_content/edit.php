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
 * @package     local_nudge\form\nudge_notification
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\form\nudge_notification_content;

// phpcs:disable moodle.Commenting.InlineComment.TypeHintingMatch

use coding_exception;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge_notification_content;
use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../lib.php');

/**
 * @package     local_nudge\form\nudge_notification
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

        $notificationoptions = $this->get_notification_options();
        $mform->addElement(
            'autocomplete',
            'nudgenotificationid',
            'Select a notification',
            $notificationoptions
        );

        $languageoptions = \get_string_manager()->get_list_of_languages();
        $mform->addElement(
            'autocomplete',
            'lang',
            'Select a language',
            $languageoptions
        );

        $mform->addElement('text', 'subject', 'Add a Subject');
        $mform->setType('subject', \PARAM_RAW);

        /** @var \core_config $CFG */
        global $CFG;
        $editor = (isset($CFG->totara_version)) ? 'htmleditor' : 'editor';
        $mform->addElement($editor, 'body', 'Add a body');
        $mform->setType('body', \PARAM_RAW);

        $this->add_action_buttons();
    }

    /**
     * @return nudge_notification_content|null
     */
    public function get_data() {
        $data = parent::get_data();

        if ($data == null) {
            return null;
        }

        return new nudge_notification_content([
            'id' => $data->id,
            'nudgenotificationid' => $data->nudgenotificationid,
            'lang' => $data->lang,
            'subject' => $data->subject,
            'body' => $data->body
        ]);
    }

    /**
     * @param nudge_notification_content $nudgenotificationcontent
     * @return void
     */
    public function set_data($nudgenotificationcontent) {
        if (!$nudgenotificationcontent instanceof nudge_notification_content) {
            throw new coding_exception(\sprintf(
                'You must provide a instance of %s to this form %s.',
                nudge_notification_content::class,
                __CLASS__
            ));
        }

        $this->_form->setDefaults([
            'id' => $nudgenotificationcontent->id,
            'nudgenotificationid' => $nudgenotificationcontent->nudgenotificationid,
            'lang' => $nudgenotificationcontent->lang,
            'subject' => $nudgenotificationcontent->subject,
            'body' => $nudgenotificationcontent->body
        ]);
    }

    /**
     * Gets an array of available {@see nudge_notification_content}s to choose from with a select.
     *
     * @return array<string|int|null, string|int|null>
     */
    private function get_notification_options() {
        $notifications = nudge_notification_db::get_all();

        return \array_combine(
            \array_column($notifications, 'id'),
            \array_column($notifications, 'title')
        );
    }
}
