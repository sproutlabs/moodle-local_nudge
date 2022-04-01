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
  * Adds a link to edit nudge notifications from the courses and categories sidebar for the root course.
  *
  * @package     local_nudge
  * @author      Liam Kearney <liam@sproutlabs.com.au>
  * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
  * @license     http://www.gnu.org/copyleft/gpl.html
  * @license     GNU GPL v3 or later
  *
  * @var \core_config        $CFG
  * @var \moodle_database    $DB
  * @var \moodle_page        $PAGE
  * @var \core_renderer      $OUTPUT
  * @var \admin_root         $ADMIN
  */

use stdClass;
use moodle_url;
use admin_externalpage;
use admin_category;
use admin_settingpage;
use admin_setting_configselect;
use admin_setting_configtext;
use admin_setting_heading;

use function get_string as gs;
use function profile_get_custom_fields;

use const PARAM_INT;

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Manage nudge notifications.
    $ADMIN->add(
        'courses',
        new admin_externalpage(
            'configurenudgenotifications',
            gs('configurenudgenotifications', 'local_nudge'),
            new moodle_url('/local/nudge/manage_notifications.php'),
            'local/nudge:configurenudgenotifications',
        ),
        (isset($CFG->totara_version))
            ? 'configurecatalog'
            : 'restorecourse'
    );

    $ADMIN->add(
        'localplugins',
        new admin_category(
            'local_nudge_settings',
            gs('pluginname', 'local_nudge'),
        )
    );

    $settingspage = new admin_settingpage(
        'managelocalnudge',
        gs('manage_settings', 'local_nudge'),
    );

    if ($ADMIN->fulltree) {
        /** @var array<stdClass> $customfields */
        $customfields = profile_get_custom_fields();
        $customfieldsselect = (count($customfields) > 0)
            ? array_combine(
                array_column($customfields, 'shortname'),
                array_column($customfields, 'name'),
            )
            : [];

        $matchonfields = [
            'idnumber' => 'Idnumber',
            'email' => 'Email',
            'id' => 'Database ID',
        ];

        $pagecontents = [
            // Manger section.
            new admin_setting_heading(
                'nudge_admin_manager_heading',
                gs('admin_manager_heading', 'local_nudge'),
                gs('admin_manager_heading_desc', 'local_nudge'),
            ),
            new admin_setting_configcheckbox(
                'local_nudge/custommangerresolution',
                gs('admin_custom_managerresolution', 'local_nudge'),
                gs('admin_custom_managerresolution_desc', 'local_nudge'),
                '0',
            ),
            new admin_setting_configselect(
                'local_nudge/managermatchonfield',
                gs('admin_manager_matchon_field', 'local_nudge'),
                gs('admin_manager_matchon_field_desc', 'local_nudge'),
                // Empty select index is less likely to cause an error.
                'idnumber',
                $matchonfields,
            ),
            new admin_setting_configselect(
                'local_nudge/managermatchwithfield',
                gs('admin_manager_matchwith_field', 'local_nudge'),
                gs('admin_manager_matchwith_field_desc', 'local_nudge'),
                '',
                $customfieldsselect,
            ),
            // UX section.
            new admin_setting_heading(
                'nudge_admin_ux_heading',
                gs('admin_ux_heading', 'local_nudge'),
                gs('admin_ux_heading_desc', 'local_nudge'),
            ),
            new admin_setting_configtext(
                'local_nudge/uxaddtranslationcount',
                gs('admin_ux_addtranslationcount', 'local_nudge'),
                gs('admin_ux_addtranslationcount_desc', 'local_nudge'),
                1,
                PARAM_INT,
            ),
            new admin_setting_configtext(
                'local_nudge/uxstartdate',
                gs('admin_ux_startdate', 'local_nudge'),
                gs('admin_ux_startdate_desc', 'local_nudge'),
                2000,
                PARAM_INT,
            ),
            new admin_setting_configcheckbox(
                'local_nudge/performancenolog',
                gs('admin_performance_nolog', 'local_nudge'),
                gs('admin_performance_nolog_desc', 'local_nudge'),
                '0',
            ),
        ];

        foreach ($pagecontents as $contentitem) {
            $settingspage->add($contentitem);
        }
    }

    $ADMIN->add('localplugins', $settingspage);
}
