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
 * Scheduled task for stats extraction.
 *
 * @package     local_composantestats
 * @copyright   2024 Laurent Guillet <laurent.guillet@univ-eiffel.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace local_composantestats\task;

defined('MOODLE_INTERNAL') || die();

class createcronstats extends \core\task\scheduled_task {

    public function get_name() {

        return get_string('createcronstats', 'local_composantestats');
    }
    
    public function execute() {
        
        global $DB;
        
        $listcomposantes = $DB->get_records('local_composantestats');
        
        foreach ($listcomposantes as $composante) {
            
            $listmodules = $DB->get_records('modules', array('visible' => 1));

            $descriptionsearched = "%".get_string('prefixdescription', 'local_synchrocohort')."%".$composante->name."%";
            
            $excludedcoursesid = "(". get_config('local_composantestats', 'excludedcourses'). ")";
            
            if ($excludedcoursesid != "()") {
            
                $sqlcoursescomposante = "SELECT * FROM {course} c LEFT JOIN "
                        . "(SELECT e.courseid, e.enrol FROM {enrol} e  LEFT JOIN {cohort} coh ON e.customint1 = coh.id WHERE"
                        . " coh.description LIKE '$descriptionsearched' AND e.enrol LIKE 'cohort') f ON c.id = f.courseid WHERE f.enrol LIKE 'cohort' "
                        . "AND c.id NOT IN $excludedcoursesid";
            } else {
                
                $sqlcoursescomposante = "SELECT * FROM {course} c LEFT JOIN "
                        . "(SELECT e.courseid, e.enrol FROM {enrol} e  LEFT JOIN {cohort} coh ON e.customint1 = coh.id WHERE"
                        . " coh.description LIKE '$descriptionsearched' AND e.enrol LIKE 'cohort') f ON c.id = f.courseid WHERE f.enrol LIKE 'cohort'";
            }

            $listcourses = $DB->get_records_sql($sqlcoursescomposante);
            
            foreach ($listmodules as $module) {
                
                if (plugin_supports('mod', $module->name, FEATURE_MOD_ARCHETYPE) == 1) {
                         
                    $isressource = true;
                } else {
                    
                    $isressource = false;
                }

                $instancecount = 0;
                $viewcount = 0;
                $usedcount = null;

                $listidallmoduleinstances = "(";

                foreach ($listcourses as $course) {

                    $listmoduleinstances  = $DB->get_records('course_modules',
                            array('course' => $course->id, 'module' => $module->id));

                    $instancecount += count($listmoduleinstances);

                    foreach ($listmoduleinstances as $moduleinstance) {

                        $listidallmoduleinstances .= $moduleinstance->id.",";
                    }
                }

                if ($listidallmoduleinstances != "(") {
                    
                    // A faire : Des Join de la mort qui tuent pour ne sélectionner que les étudiants 
                    // (on a pas besoin de restreindre aux étudiants de la bonne cohorte).

                    $listidallmoduleinstances = substr_replace($listidallmoduleinstances, ")", -1, 1);

                    // Trouver un moyen de faire une requête plus économique ou l'externaliser dans une tâche.

                    $sqlviewedcount = "SELECT COUNT(DISTINCT log.id) FROM {logstore_standard_log} AS log
                        LEFT JOIN {role_assignments} AS ra ON log.userid = ra.userid 
                        LEFT JOIN {context} AS con1 ON con1.id = ra.contextid 
                        LEFT JOIN {course} AS co ON co.id = con1.instanceid 
                        WHERE ra.roleid = 5 AND con1.contextlevel = 50 
                        AND log.component LIKE 'mod_$module->name' AND log.target LIKE 'course_module' 
                        AND log.contextinstanceid IN $listidallmoduleinstances"
                            . " AND log.action LIKE 'viewed'";
        
                    $viewcount = $DB->count_records_sql($sqlviewedcount);
                    
                    if (!$isressource) {
                        
                        $sqlusagecount = "SELECT COUNT(DISTINCT log.id) FROM {logstore_standard_log} AS log
                        LEFT JOIN {role_assignments} AS ra ON log.userid = ra.userid 
                        LEFT JOIN {context} AS con1 ON con1.id = ra.contextid 
                        LEFT JOIN {course} AS co ON co.id = con1.instanceid 
                        WHERE ra.roleid = 5 AND con1.contextlevel = 50 
                        AND log.component LIKE 'mod_$module->name' 
                        AND log.contextinstanceid IN $listidallmoduleinstances"
                            . " AND log.action NOT LIKE 'viewed'";
        
                        $usedcount = $DB->count_records_sql($sqlusagecount);
                    }
                }
                
                if ($DB->record_exists('local_cronstats',
                        array('composanteid' => $composante->id, 'modid' => $module->id))) {
                    
                    $statsrecord = $DB->get_record('local_cronstats',
                        array('composanteid' => $composante->id, 'modid' => $module->id));
                    
                    $statsrecord->usagecount = $instancecount;
                    $statsrecord->statsviewed = $viewcount;
                    $statsrecord->statsused = $usedcount;
                    
                    $DB->update_record('local_cronstats', $statsrecord);
                } else {
                    
                    $statsrecord = new \stdClass();
                    
                    $statsrecord->composanteid = $composante->id;
                    $statsrecord->modid = $module->id;
                    $statsrecord->usagecount = $instancecount;
                    $statsrecord->statsviewed = $viewcount;
                    $statsrecord->statsused = $usedcount;
                    
                    $DB->insert_record('local_cronstats', $statsrecord);
                }
                
                mtrace("Composante : $composante->name, Module : $module->name, Ressource : $isressource, "
                        . "Nombre instance : $instancecount, Nombre vue : $viewcount,"
                        . " Nombre utilisations : $usedcount");
            }
        }
    }
}