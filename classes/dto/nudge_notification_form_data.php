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
 * This class is the base entity representation.
 *
 * @package     local_nudge\dto
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\dto;

use coding_exception;
use local_nudge\local\nudge_notification_content;
use local_nudge\local\nudge_notification;

/**
 * @package     local_nudge\dto
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 */
class nudge_notification_form_data {
    public nudge_notification $notification;

    /**
     * @var array<nudge_notification_content>
     */
    public array $notificationcontents;

    public function __construct(
        nudge_notification $notification,
        array $notificationcontents
    ) {
        foreach ($notificationcontents as $notificationcontent) {
            if (!$notificationcontent instanceof nudge_notification_content) {
                throw new coding_exception(\sprintf(
                    'You must pass an instance of %s as notificationcontent',
                    nudge_notification_content::class
                ));
            }
        }

        $this->notification = $notification;
        $this->notificationcontents = $notificationcontents;
    }
}
