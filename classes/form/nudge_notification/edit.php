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

namespace local_nudge\form\nudge_notification;

use coding_exception;
use local_nudge\dml\nudge_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\dto\nudge_notification_form_data;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;
use local_nudge\local\nudge_notification_content;
use moodleform;

use function \get_string as gs;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->libdir/formslib.php");
require_once(__DIR__ . '/../../../lib.php');

/**
 * @package     local_nudge\form\nudge_notification
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class edit extends moodleform {

    /**
     * Form value to data value.
     *
     * @var array<string, string>
     */
    private const RECURRING_FIELDS = [
        'contentid' => 'id',
        'lang' => 'lang',
        'subject' => 'subject',
        'body' => 'body'
    ];

    /**
     * ID of the current {@link nudge_notification}
     */
    private int $id = 0;

    public function __construct($id = null) {
        if (\is_int($id) && $id) {
            if (nudge_db::get_by_id($id) === null) {
                throw new \exception('TODO');
            }
            $this->id = $id;
        }

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition() {
        /** @var \moodle_database $DB */
        global $DB;

        /** @var \core_config $CFG */
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', \PARAM_INT);

        $mform->addElement('text', 'title', 'Add a title');
        $mform->setType('title', \PARAM_RAW);

        $userquery = $DB->get_records_sql(<<<SQL
            SELECT
                id,
                CONCAT(firstname, ' ', lastname) as fullname
            FROM
                {user}
        SQL);

        $useroptions = \array_combine(
            \array_column($userquery, 'id'),
            \array_column($userquery, 'fullname')
        );

        $mform->addElement(
            'autocomplete',
            'userfromid',
            'Select a user as the sender for this email',
            $useroptions
        );

        // Add some help around the template variables.
        $helpitems = '';
        foreach (nudge::TEMPLATE_VARIABLES as $templatevariable) {
            [$templateobj, $templatename] = \explode('_', \trim($templatevariable, '{}'));
            $helpitems .= "<li><code>$templatevariable</code> -> {$templateobj}'s {$templatename}</li>";
        }
        $mform->addElement('header', 'templatevarinfohdr', 'Template Infomation');
        $mform->addElement(
            'html',
            <<<HTML
                <em>You can use the following properties in a translation:<em>
                <ul>
                    {$helpitems}
                </ul>
            HTML
        );

        $languageoptions = \get_string_manager()->get_list_of_languages();

        $editor = (isset($CFG->totara_version)) ? 'htmleditor' : 'editor';

        $groupelements = [
            $mform->createElement('hidden', 'contentid'),
            $mform->createElement('header', 'translationhdr', "Translation"),
            $mform->createElement('autocomplete', 'lang', 'Select a language', $languageoptions),
            $mform->createElement('text', 'subject', 'Add a Subject'),
            $mform->createElement($editor, 'body', 'Add a body')
        ];

        $repeatcount = ($this->id > 0)
            // TODO: handle ids that don't exist. Maybe on instancing.
            ? count(nudge_notification_db::get_by_id($this->id)->get_contents())
            : 1;

        $this->repeat_elements(
            $groupelements,
            $repeatcount,
            [
                'contentid' => [
                    'type' => \PARAM_INT
                ],
                'subject' => [
                    'type' => \PARAM_RAW
                ],
                'body' => [
                    'type' => \PARAM_RAW
                ]
            ],
            'hiddenrepeat',
            'add',
            1,
            'Add {no} more translation',
        );

        $this->add_action_buttons();
    }

    /**
     * Logic in the forms layer ¯\_(ツ)_/¯ to package to make the form return nicer.
     *
     * @return nudge_notification_form_data|null
     */
    public function get_data() {
        $data = parent::get_data();

        if ($data == null) {
            return null;
        }

        // Restructure the weirdly shaped data into nudge_notification_contents (translations).
        /** @var array<nudge_notification_content> */
        $notificationcontents = [];

        // I dunno just using the first lot since its not structure properly..
        $submittedtranslationcount = count($data->lang);

        for ($i = 0; $i < $submittedtranslationcount; $i++) {
            /** @var array<string, string> */
            $translationdata = [];

            foreach (self::RECURRING_FIELDS as $formvalue => $datavalue) {
                $translationdata[$datavalue] = $data->{$formvalue}[$i];
            }

            $notificationcontents[] = new nudge_notification_content($translationdata);
        }

        $notification = new nudge_notification([
            'id' => ($data->id === 0) ? null : $data->id,
            'userfromid' => $data->userfromid,
            'title' => $data->title
        ]);

        // Package both into a DTO.
        return new nudge_notification_form_data(
            $notification,
            $notificationcontents
        );
    }

    /**
     * Populates from a DTO.
     *
     * @param nudge_notification_form_data $nudgenotificationformdata
     * @return void
     */
    public function set_data($nudgenotificationformdata) {
        if (!$nudgenotificationformdata instanceof nudge_notification_form_data) {
            throw new coding_exception(\sprintf(
                'You must provide a instance of %s to this form %s.',
                nudge_notification_form_data::class,
                __CLASS__
            ));
        }

        $notificationcount = \count($nudgenotificationformdata->notificationcontents);

        $defaults = [
            'id' => $nudgenotificationformdata->notification->id,
            'userfromid' => $nudgenotificationformdata->notification->userfromid,
            'title' => $nudgenotificationformdata->notification->title,
            'hiddenrepeat' => $notificationcount,
        ];

        for ($i = 0; $i < $notificationcount; $i++) {
            $notificationcontent = $nudgenotificationformdata->notificationcontents[$i];

            // Setup a nice custom header.
            $langlist = \get_string_manager()->get_list_of_languages();
            $language = $langlist[$notificationcontent->lang] ?? 'Unknown Translation';
            $defaults["translationhdr[{$i}]"] = "Translation - {$language}: {$notificationcontent->subject}";

            foreach (self::RECURRING_FIELDS as $formvalue => $datavalue) {
                // Moodle has a CRAZY API, It's just old and given that they are doing well :)
                // Example: With index 0 and the body field:
                // $defaults["body['0']"] = $notificationcontent->body;
                $defaults["{$formvalue}[{$i}]"] = $notificationcontent->{$datavalue};
            }

        }

        $this->_form->setDefaults($defaults);
    }
}
