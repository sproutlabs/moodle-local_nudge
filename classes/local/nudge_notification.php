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
 * @package     local_nudge\local
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\local;

use local_nudge\dml\nudge_db;
use local_nudge\dml\nudge_notification_content_db;
use local_nudge\dto\nudge_notification_form_data;
use local_nudge\local\abstract_nudge_entity;
use local_nudge\local\nudge_notification_content;
use moodle_exception;
use stdClass;

class nudge_notification extends abstract_nudge_entity {

    /** {@inheritDoc} */
    const DEFAULTS = [
        'title' => 'Untitled Notification'
    ];

    /**
     * @var string|null Title for this {@see nudge_notification}
     */
    public $title = null;

    /**
     * @var int|null The id of a {@see core_user} to send notifications from.
     */
    public $userfromid = null;

    /**
     * Returns the contents for this nudge notification.
     * Filterable using a specific langcode (See MOODLE docs<https://docs.moodle.org/dev/Table_of_locales>).
     *
     * @param string $langcode
     * @return array<nudge_notification_content>
     */
    public function get_contents($langcode = null) {
        $filter = [
            'nudgenotificationid' => $this->id,
        ];

        if ($langcode) {
            $filter['lang'] = $langcode;
        }

        return nudge_notification_content_db::get_all_filtered($filter);
    }

    public function get_notification_edit_link(): string {
        /** @var \core_config $CFG */
        global $CFG;

        $link = "{$CFG->wwwroot}/local/nudge/edit_notification.php?id={$this->id}";

        $linktitle = get_string('notification_edit_link', 'local_nudge', $this->title);

        $linkhtml = <<<HTML
            <a href="{$link}">{$linktitle}</a>
        HTML;

        return $linkhtml;
    }

    /**
     * Returns $this wrapped in a {@link nudge_notification_form_data} with it's linked {@link nudge_notification_content}s.
     *
     * @return nudge_notification_form_data
     */
    public function as_notification_form(): nudge_notification_form_data {
        $contents = $this->get_contents();

        return new nudge_notification_form_data(
            $this,
            $contents
        );
    }

    /**
     * Gets the attached notification content for a specific user based on their language.
     *
     * @param \core\entity\user|stdClass $user
     * @return nudge_notification_content
     */
    public function get_users_contents(stdClass $user) {
        $userslangcode = nudge_get_user_language_code($user);
        $notificationcontents = $this->get_contents($userslangcode);
        $notificationcontent = array_shift($notificationcontents);

        // This case happens when a user's prefered language is not present on the notification.
        // In this case we just pick the first (Primary key based) contents to use and append a simple
        // message informing the user that their language is not supported.
        if ($notificationcontent === null) {
            if (debugging()) {
                mtrace("Failed to resolve language for user {$user->id} while sending notification {$this->id}");
            }
            $allpossiblecontents = $this->get_contents();
            $notificationcontent = array_shift($allpossiblecontents);

            // Form validation means this should be unreachable.
            if ($notificationcontent === null) {
                throw new moodle_exception(
                    'expectedunreachable',
                    'local_nudge'
                );
            }
            $languagelist = \get_string_manager()->get_list_of_languages();
            $languagenotsupportedwarning = get_string(
                'languagenotsupported',
                'local_nudge',
                [
                    'langcode' => $userslangcode,
                    'language' => $languagelist[$userslangcode] ?? 'ERROR: We can\'t offer a language name for this language code',
                ]
            );
            $notificationcontent->body .= <<<HTML
                <br/>
                <br/>
                {$languagenotsupportedwarning}
            HTML;
        }

        return $notificationcontent;
    }

    /**
     * @codeCoverageIgnore More or less static return.
     *
     * @return array<mixed>
     */
    public function get_summary_fields(): array {
        /** @var \moodle_database $DB */
        global $DB;

        $notificationcount = count($this->get_contents());
        $possibleplurals = ($notificationcount != 1) ? 's' : '';
        $pluralreferer = ($notificationcount > 1) ? 'are' : 'is';
        $linkednudgecount = $DB->count_records_select(nudge_db::$table, <<<SQL
        linkedlearnernotificationid = ? OR linkedmanagernotificationid = ?
        SQL, [$this->id, $this->id]); // Yeah this is the best way todo multiple indentical params, moodle is great..
        return [
            $this->get_notification_edit_link(),
            <<<HTML
                <p class="badge badge-primary">There {$pluralreferer} {$notificationcount} linked translation{$possibleplurals}</p>
            HTML,
            <<<HTML
                <p class="badge badge-info">$linkednudgecount linked nudges</p>
            HTML
        ];
    }

    protected function cast_fields(): void {
        $this->title = (string) $this->title;
        $this->userfromid = (int) $this->userfromid;
    }
}
