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
use core_user;
use local_nudge\dml\nudge_notification_db;
use local_nudge\dto\nudge_notification_form_data;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;
use local_nudge\local\nudge_notification_content;
use moodleform;

use function get_config;
use function get_string;

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
            if (nudge_notification_db::get_by_id($id) === null) {
                throw new \moodle_exception('nudgenotificationdoesntexist', 'local_nudge', '', $id);
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

        $mform->addElement(
            'text',
            'title',
            get_string('form_notification_title', 'local_nudge')
        );
        $mform->setType('title', \PARAM_TEXT);
        $mform->addRule('title', get_string('validation_notification_needtitle', 'local_nudge'), 'required');

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
            get_string('form_notification_userfrom', 'local_nudge'),
            $useroptions
        );
        $mform->addRule('userfromid', get_string('validation_notification_needsender', 'local_nudge'), 'required');

        // Add some help around the template variables.
        $helptext = get_string('form_notification_templatevar_help', 'local_nudge');
        /** @var array<string> */
        $helpitems = [];
        foreach (\array_keys(nudge::TEMPLATE_VARIABLES) as $templatevariable) {
            [$templateobj, $templatename] = \explode('_', \trim($templatevariable, '{}'));
            $helpitems[] = "<li><code>$templatevariable</code> -> {$templateobj}'s {$templatename}</li>";
        }
        $helpitems = \implode('', $helpitems);
        $mform->addElement('header', 'templatevarinfohdr', get_string('form_notification_templatevar_title', 'local_nudge'));
        $mform->setExpanded('templatevarinfohdr');
        $mform->addElement(
            'html',
            <<<HTML
                <em>{$helptext}<em>
                <ul>
                    {$helpitems}
                </ul>
            HTML
        );

        $languageoptions = \get_string_manager()->get_list_of_languages();

        $editor = (isset($CFG->totara_version)) ? 'htmleditor' : 'editor';

        $groupelements = [
            $mform->createElement('hidden', 'contentid'),
            $mform->createElement('header', 'translationhdr', get_string('form_notification_translation_header', 'local_nudge')),
            $mform->createElement(
                'autocomplete',
                'lang',
                get_string('form_notification_selectlang', 'local_nudge'),
                $languageoptions
            ),
            $mform->createElement('text', 'subject', get_string('form_notification_addsubject', 'local_nudge')),
            $mform->createElement($editor, 'body', get_string('form_notification_addbody', 'local_nudge'))
        ];

        $repeatcount = ($this->id > 0)
            ? count(nudge_notification_db::get_by_id($this->id)->get_contents())
            : 1;

        // Pluralise the add label if required.
        $addcount = get_config('local_nudge', 'uxaddtranslationcount');
        $repeatlabel = \strtr(get_string('form_notification_addprompt', 'local_nudge'), [
            '{possible_s}' => ($addcount > 1) ? 's' : ''
        ]);

        $this->repeat_elements(
            $groupelements,
            $repeatcount,
            [
                'contentid' => [
                    'type' => \PARAM_INT,
                ],
                'lang' => [
                    'rule' => 'required'
                ],
                'subject' => [
                    'type' => \PARAM_RAW,
                    'rule' => 'required'
                ],
                'body' => [
                    'type' => \PARAM_RAW,
                    'rule' => 'required'
                ]
            ],
            'hiddenrepeat',
            'add',
            $addcount,
            $repeatlabel,
        );

        $mform->addElement('header', 'metahdr', get_string('form_metahdr', 'local_nudge'));
        $mform->addElement('static', 'createdby', 'Created by');
        $mform->addElement('static', 'timecreated', 'Time created');
        $mform->addElement('static', 'lastmodifiedby', 'Last modified by');
        $mform->addElement('static', 'lastmodified', 'Last modified at');

        $this->add_action_buttons();
    }

    /**
     * Logic in the forms layer ¯\_(ツ)_/¯ to package to make the form return nicer.
     *
     * @return nudge_notification_form_data|null
     */
    public function get_data() {
        /** @var \core_config $CFG */
        global $CFG;

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

            // Moodle's newer editor returns some nested stuff n things.
            if (!isset($CFG->totara_version)) {
                $data->body[$i] = $data->body[$i]['text'];
            }

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
        /** @var \core_config $CFG */
        global $CFG;

        if (!$nudgenotificationformdata instanceof nudge_notification_form_data) {
            throw new coding_exception(\sprintf(
                'You must provide a instance of %s to this form %s.',
                nudge_notification_form_data::class,
                __CLASS__
            ));
        }

        $notification = $nudgenotificationformdata->notification;
        $notificationcount = \count($nudgenotificationformdata->notificationcontents);

        $defaults = [
            'id' => $notification->id,
            'userfromid' => $notification->userfromid,
            'title' => $notification->title,
            'hiddenrepeat' => $notificationcount,
            'createdby' => get_string('form_noyetset', 'local_nudge'),
            'timecreated' => get_string('form_noyetset', 'local_nudge'),
            'lastmodifiedby' => get_string('form_noyetset', 'local_nudge'),
            'lastmodified' => get_string('form_noyetset', 'local_nudge'),
        ];

        for ($i = 0; $i < $notificationcount; $i++) {
            $notificationcontent = $nudgenotificationformdata->notificationcontents[$i];

            // Setup a nice custom header.
            $langlist = \get_string_manager()->get_list_of_languages();
            $language = $langlist[$notificationcontent->lang] ?? 'Unknown Translation';

            $langdata = [
                'language' => $language,
                'subject' => $notificationcontent->subject,
            ];

            $translationheader = get_string(
                'form_notification_translation_template',
                'local_nudge',
                $langdata
            );
            $defaults["translationhdr[{$i}]"] = $translationheader;

            foreach (self::RECURRING_FIELDS as $formvalue => $datavalue) {
                // Moodle's newer editor returns some nested stuff n things.
                if (!isset($CFG->totara_version) && $datavalue === 'body') {
                    $defaults["{$formvalue}[{$i}]"]['text'] = $notificationcontent->{$datavalue};
                    continue;
                }
                // Example: With index 0 and the body field:
                // $defaults["body['0']"] = $notificationcontent->body;
                $defaults["{$formvalue}[{$i}]"] = $notificationcontent->{$datavalue};
            }
        }

        // Format in the metadata.
        foreach (['createdby', 'lastmodifiedby'] as $usermetadata) {
            if (($notification->{$usermetadata} ?? 0) !== 0) {
                $defaults[$usermetadata] = fullname(core_user::get_user($notification->{$usermetadata}));
            }
        }
        foreach (['timecreated', 'lastmodified'] as $timemetadata) {
            if (($notification->{$timemetadata} ?? 0) > 0) {
                $defaults[$timemetadata] = \date(
                    nudge::DATE_FORMAT_NICE,
                    $notification->{$timemetadata}
                );
            }
        }

        $this->_form->setDefaults($defaults);
    }

    /**
     * Validation of this form.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $files
     * @return array<string, string>
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate it: Only has one translation for each language.
        if (!empty($data['lang'])) {

            $langs = $data['lang'];

            if (count($langs) !== count(\array_unique($langs))) {
                // If duplicates are found mark every single language as invalid.
                // Not sure if there is a better element to display this on.
                for ($i = 0; $i < count($langs); $i++) {
                    $errors["lang[{$i}]"] = get_string(
                        'validation_notification_duplicatelangs',
                        'local_nudge'
                    );
                }
            }
        }

        return $errors;
    }
}
