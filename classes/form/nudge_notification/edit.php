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

namespace local_nudge\form\nudge_notification;

// VSCODE's current pluginset doesn't support typehinted global so we have to type hint them in the local scope.
// phpcs:disable moodle.Commenting.InlineComment.TypeHintingMatch
// phpcs:disable moodle.Commenting.InlineComment.DocBlock

use coding_exception;
use local_nudge\local\nudge_notification;
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
        /** @var \moodle_database $DB */
        global $DB;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', \PARAM_INT);

        $mform->addElement('text', 'title', 'Add a title');
        $mform->setType('title', \PARAM_RAW);

        // TODO fix double loop.
        $userquery = $DB->get_records_sql(<<<SQL
            SELECT
                id,
                CONCAT(firstname, ' ', lastname) as fullname
            FROM
                {user}
        SQL);

        $useroptions = [];
        foreach ($userquery as $user) {
            $useroptions[$user->id] = $user->fullname;
        }

        $mform->addElement(
            'autocomplete',
            'userfromid',
            'Select a user as the from for this email',
            $useroptions
        );

        $this->add_action_buttons();
    }

    /**
     * @return nudge_notification|null
     */
    public function get_data() {
        $data = parent::get_data();

        if ($data == null) {
            return null;
        }

        return new nudge_notification([
            'id' => $data->id,
            'userfromid' => $data->userfromid,
            'title' => $data->title
        ]);
    }

    /**
     * @param nudge_notification $nudgenotification
     * @return void
     */
    public function set_data($nudgenotification) {
        if (!$nudgenotification instanceof nudge_notification) {
            throw new coding_exception(\sprintf(
                'You must provide a instance of %s to this form %s.',
                nudge_notification::class,
                __CLASS__
            ));
        }

        $this->_form->setDefaults([
            'id' => $nudgenotification->id,
            'userfromid' => $nudgenotification->userfromid,
            'title' => $nudgenotification->title
        ]);
    }
}
