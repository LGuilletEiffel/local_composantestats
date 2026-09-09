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
 * Scheduled task.
 *
 * @package     local_composantestats
 * @copyright   2024 Laurent Guillet <laurent.guillet@univ-eiffel.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace local_composantestats\task;

defined('MOODLE_INTERNAL') || die();

class checkcomposantenames extends \core\task\scheduled_task {

    public function get_name() {

        return get_string('checkcomposantenames', 'local_composantestats');
    }
    
    public function execute() {

        global $DB;
        
        $cohortcategory = get_config('synchrocohort')->cohortcategory;
        
        $contextcohortcategory = $DB->get_record('context',
                array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $cohortcategory))->id;
        
        $sqlcohorts = "SELECT DISTINCT description FROM {cohort}"
                . " WHERE contextid = $contextcohortcategory AND description NOT LIKE ''";
        
        print_object($sqlcohorts);
        
        $listcohortsdescription = $DB->get_records_sql($sqlcohorts);
        
        foreach ($listcohortsdescription as $cohortdescription) {
            
            $truncateddescription = substr($cohortdescription->description,
                    strlen(get_string('prefixdescription', 'local_synchrocohort')));
            
                if (!$DB->record_exists('local_composantestats', array('name' => $truncateddescription))) {
                    
                    $composante = new \stdClass();
                    $composante->name = $truncateddescription;
                    
                    $DB->insert_record('local_composantestats',$composante);
                }
        }
    }

}