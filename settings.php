<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     local_composantestats
 * @category    admin
 * @copyright   2024 Laurent Guillet <laurent.guillet@univ-eiffel.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    
    $settings = new admin_settingpage('local_composantestats_settings', get_string('pluginname', 'local_composantestats'));

    $ADMIN->add('localplugins', $settings);
    
    $listcomposantes = $DB->get_records('local_composantestats');
    
    foreach ($listcomposantes as $composante) {
        
        $composanteid = $composante->id;
        
        $namesetting = 'local_composantestats/composanteuser'.$composanteid;
        
        $settings->add(new admin_setting_users_with_capability(
                $namesetting,
                get_string('composanteuser', 'local_composantestats', $composante->name),
                null,
                null,
                'local/composantestats:canuseplugin'
        ));
    }
    
    $settings->add(new admin_setting_configtext('local_composantestats/startdategraph',
                    get_string('startdategraph', 'local_composantestats'),
                    get_string('dateformatexplanation', 'local_composantestats'),
                    null
    ));
    
    $settings->add(new admin_setting_configtext('local_composantestats/excludedcourses',
                    get_string('excludedcourses', 'local_composantestats'),
                    null,
                    null
    ));
}
