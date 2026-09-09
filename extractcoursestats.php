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
 * View the statistics
 *
 * @package    local_composantestats
 * @author     Laurent GUILLET <laurent.guillet@univ-eiffel.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');

$systemcontext = context_system::instance();

$redirecturlhometemp = new moodle_url('/my');

require_login();

if (!is_siteadmin()) {

    redirect($redirecturlhometemp);
}

$csvexporter = new csv_export_writer('semicolon');
$csvexporter->set_filename(mb_convert_encoding("Export statistiques SIO-IP", 'ISO-8859-1'));



$columnnames = array(mb_convert_encoding("Cours/Section", 'ISO-8859-1'), mb_convert_encoding("Nombre étudiants/actions", 'ISO-8859-1'));
$csvexporter->add_data($columnnames);

extractstatcourse(5941, $csvexporter);
extractstatcourse(5798, $csvexporter);
extractstatcourse(5940, $csvexporter);

$sqlstudentallcourses = "SELECT COUNT (DISTINCT ula.userid)
FROM mdl_role_assignments ra
JOIN mdl_user u ON u.id = ra.userid
JOIN mdl_role r ON r.id = ra.roleid
JOIN mdl_context ctx ON ctx.id = ra.contextid
JOIN mdl_course c ON c.id = ctx.instanceid
JOIN mdl_user_lastaccess ula ON ula.courseid = c.id AND ula.userid = u.id
WHERE c.id IN (5941, 5798, 5940) AND ra.roleid = 5";

$countstudentallcourses = $DB->count_records_sql($sqlstudentallcourses);

$lineallcourses = array(mb_convert_encoding("Etudiants connectés pour l'ensemble des cours", 'ISO-8859-1'), $countstudentallcourses);
$csvexporter->add_data($lineallcourses);


$csvexporter->download_file();

function extractstatcourse ($courseid, $csvexportwriter) {
    
    global $DB;
    
    $coursename = $DB->get_record('course', array('id' => $courseid))->fullname;
   
    $sqlstudentcourse = "SELECT COUNT (ula.*)
        FROM mdl_role_assignments ra
        JOIN mdl_user u ON u.id = ra.userid
        JOIN mdl_role r ON r.id = ra.roleid
        JOIN mdl_context ctx ON ctx.id = ra.contextid
        JOIN mdl_course c ON c.id = ctx.instanceid
        JOIN mdl_user_lastaccess ula ON ula.courseid = c.id AND ula.userid = u.id
        WHERE c.id = $courseid AND ra.roleid = 5";
    
    $countstudentcourse = $DB->count_records_sql($sqlstudentcourse);
    
    $line1 = array(mb_convert_encoding("$coursename Etudiants connectés", 'ISO-8859-1'), $countstudentcourse);
    $csvexportwriter->add_data($line1);
    
    $sqlviewedcourse = "SELECT COUNT (log.*)
        FROM mdl_logstore_standard_log log
            JOIN mdl_role_assignments ra ON log.userid = ra.userid
            JOIN mdl_user u ON u.id = ra.userid 
            JOIN mdl_role r ON r.id = ra.roleid 
            JOIN mdl_context ctx ON ctx.id = ra.contextid
            JOIN mdl_course c ON c.id = ctx.instanceid 
            WHERE c.id = $courseid AND ra.roleid = 5 AND log.action LIKE 'viewed' AND log.courseid = $courseid";
    
    $countviewedcourse = $DB->count_records_sql($sqlviewedcourse);
    
    $line2 = array(mb_convert_encoding("$coursename Utilisation des activités", 'ISO-8859-1'), $countviewedcourse);
    $csvexportwriter->add_data($line2);
    
    $listsections = $DB->get_records('course_sections', array('course' => $courseid));

    foreach ($listsections as $section) {

        $listmodules = $DB->get_records('course_modules', array('section' => $section->id));

        $listcontextmodules = "(";

        foreach ($listmodules as $module) {

            $modulecontextid = $DB->get_record('context', array('contextlevel' => CONTEXT_MODULE, 'instanceid' => $module->id))->id;

            $listcontextmodules .= "$modulecontextid, ";
        }
        
        $countviewedsection = 0;
        
        if ($listcontextmodules != "(") {

            $listcontextmodules = substr_replace($listcontextmodules, ")", -2, 1);

            $sqlviewedsection = "SELECT COUNT (log.*)
            FROM mdl_logstore_standard_log log
                    JOIN mdl_role_assignments ra ON log.userid = ra.userid
                    JOIN mdl_user u ON u.id = ra.userid 
                    JOIN mdl_role r ON r.id = ra.roleid 
                    JOIN mdl_context ctx ON ctx.id = ra.contextid 
                    WHERE ra.roleid = 5 AND log.action LIKE 'viewed' AND log.contextid IN $listcontextmodules";

            $countviewedsection = $DB->count_records_sql($sqlviewedsection);
        }
        
        $sectionname = $section->name;
        
        if ($section->name == null || $section->name == "") {
            
            $sectionname = $section->section;
        }
        
        $linesection = array(mb_convert_encoding("$coursename Utilisation des activités Section $sectionname", 'ISO-8859-1'), $countviewedsection);
        
        $csvexportwriter->add_data($linesection);
    }
}