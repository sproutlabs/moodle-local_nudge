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
 * DML for {@see nudge_notification}
 * @package     local_nudge\dml
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\dml;

use core_user;
use local_nudge\local\nudge;
use local_nudge\local\nudge_notification;
use stdClass;

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
    public static function on_before_create(&$notification): void {
        if ($notification->userfromid === 0) {
            $notification->userfromid = core_user::get_noreply_user()->id;
        }
    }

    /**
     * Override to unset relations and disable linked nudge.
     *
     * {@inheritDoc}
     */
    public static function delete(?int $id = null): void {
        // TODO, These need to be disabled and a notification needs to be sent to an admin.
        // Could just use an SQL key here.
        $notification = self::get_by_id($id);
        parent::delete($id);

        /** @var \local_nudge\local\nudge[] */
        $lremoves = nudge_db::get_all_filtered(['linkedlearnernotificationid' => $id]);

        foreach ($lremoves as $lremove) {
            $lremove->linkedlearnernotificationid = 0;
            $lremove->isenabled = false;
            nudge_db::save($lremove);
            self::send_deletion_message($lremove, $notification);
        }

        /** @var \local_nudge\local\nudge[] */
        $mremoves = nudge_db::get_all_filtered(['linkedmanagernotificationid' => $id]);

        foreach ($mremoves as $mremove) {
            $mremove->linkedmanagernotificationid = 0;
            $mremove->isenabled = false;
            nudge_db::save($mremove);
            self::send_deletion_message($mremove, $notification);
        }
    }

    /**
     * Templates a deletion message.
     *
     * @param nudge $nudge
     * @param nudge_notification $notification
     *
     * @return void
     */
    private static function send_deletion_message(nudge $nudge, nudge_notification $notification): void {
        /** @var stdClass $SITE */
        global $SITE;

        $templatedata = new stdClass();
        $templatedata->sitefullname = $SITE->fullname;
        $templatedata->nudgetitle = $nudge->title;
        $templatedata->notificationtitle = $notification->title;

        $nudge->notify_owners(
            \get_string(
                'nudge_exception_unlinked_notification_subject',
                'local_nudge'
            ),
            \get_string(
                'nudge_exception_unlinked_notification_body',
                'local_nudge',
                $templatedata
            )
        );
    }
}
