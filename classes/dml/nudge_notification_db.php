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
 * DML for {@see nudge_notification}
 * @package     local_nudge\dml
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\dml;

use core_user;
use local_nudge\local\nudge_notification;

/**
 * {@inheritDoc}
 * @extends abstract_nudge_db<nudge_notification>
 */
class nudge_notification_db extends abstract_nudge_db {

    /** {@inheritdoc} */
    public static $table = 'local_nudge_notification';

    /** {@inheritdoc} */
    public static $entityclass = nudge_notification::class;

    /**
     * Ensure the no-reply user is the sender even if form validation fails.
     *
     * @param nudge_notification $notification
     */
    public static function on_before_create($notification): void {
        if ($notification->userfromid == null) {
            $notification->userfromid = core_user::get_noreply_user()->id;
        }
    }

    /**
     * Override to unset relations.
     *
     * @todo Refractor this to a hook.
     *
     * {@inheritDoc}
     */
    public static function delete(?int $id = null): void {
        // Could just use that SQL thingie here.
        parent::delete($id);

        $lremoves = nudge_db::get_all_filtered(['linkedlearnernotificationid' => $id]);
        $mremoves = nudge_db::get_all_filtered(['linkedmanagernotificationid' => $id]);

        // TODO, These need to be disabled and a notification needs to be sent to an admin.

        foreach ($lremoves as $remove) {
            $remove->linkedlearnernotificationid = 0;
            nudge_db::save($remove);
        }

        foreach ($mremoves as $remove) {
            $remove->linkedmanagernotificationid = 0;
            nudge_db::save($remove);
        }
    }
}
