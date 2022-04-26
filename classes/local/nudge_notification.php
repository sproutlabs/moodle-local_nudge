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

use local_nudge\dml\nudge_notification_content_db;
use local_nudge\dto\nudge_notification_form_data;
use local_nudge\local\abstract_nudge_entity;
use local_nudge\local\nudge_notification_content;

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
     * Returns the content for a language code.
     *
     * @param string $langcode
     * @return array<nudge_notification_content|null>
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
     * @codeCoverageIgnore More or less static return.
     *
     * @return array<mixed>
     */
    public function get_summary_fields(): array {
        $notificationcount = count($this->get_contents());
        return [
            $this->id,
            $this->get_notification_edit_link(),
            <<<HTML
                <p class="badge badge-primary">There are {$notificationcount} linked translations</p>
            HTML
        ];
    }

    protected function cast_fields(): void {
        $this->title = (string) $this->title;
        $this->userfromid = (int) $this->userfromid;
    }
}
