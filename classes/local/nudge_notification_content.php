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

use local_nudge\dml\nudge_notification_db;
use local_nudge\local\abstract_nudge_entity;
use local_nudge\local\nudge_notification;

class nudge_notification_content extends abstract_nudge_entity {

    /** {@inheritDoc} */
    const DEFAULTS = [
        'nudgenotificationid' => 0,
        'lang' => 'en',
        'subject' => 'You just got a nudge, Your educator would like to reminder you of some learning.',
        'body' => <<<HTML
        <p>Hi {user_firstname} {user_lastname}, You are receiving have some unfinished learning from course: {course_fullname}.</p>
        <p>You can return to this course to complete it here: {course_link}.</p>
        <p>Hope this helps! If you have any questions you can reach out @ {sender_email}</p>
        HTML
    ];

    /**
     * @var int|null Foreign key for has_one to {@see nudge_notification}.
     */
    public $nudgenotificationid = null;

    /**
     * @var string|null Lang code representing the language this content is intended for.
     */
    public $lang = null;

    /**
     * @var string|null The subject for this notification event.
     */
    public $subject = null;

    /**
     * @var string|null The body of the email to send
     */
    public $body = null;

    /**
     * Gets the associated {@see nudge_notification}
     * @return nudge_notification|null
     */
    public function get_notification() {
        // Default notification.
        if (\intval($this->nudgenotificationid) === 0) {
            return null;
        }
        return nudge_notification_db::get_by_id(\intval($this->nudgenotificationid));
    }

    protected function cast_fields(): void {
        $this->nudgenotificationid = (int) $this->nudgenotificationid;
        $this->lang = (string) $this->lang;
        $this->subject = (string) $this->subject;
        $this->body = (string) $this->body;
    }
}
