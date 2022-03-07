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
 * @package     local_nudge\local
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\local;

use local_nudge\dml\nudge_notification_content_db;
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
     * @var int|null Last modified time stored as a timestamp.
     */
    public $lastmodified = null;

    /**
     * @var int|null The id of a {@see core_user} to send notifications from.
     */
    public $userfromid = null;

    /**
     * Returns the content for a language code.
     *
     * @param string $langcode
     * @return nudge_notification_content|null
     */
    public function get_content_for_lang($langcode = 'en') {
        return nudge_notification_content_db::get_filtered([
            'nudgenotificationid' => $this->id,
            'lang' => $langcode
        ]);
    }

    /**
     * @return array<mixed>
     */
    public function get_summary_fields() {
        return [
            $this->id,
            $this->title
        ];
    }
}
