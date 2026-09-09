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

global $USER, $DB, $PAGE;

require_once(__DIR__ . '/../../config.php');

$tab = optional_param('tab', 1, PARAM_INT);
$composanteid = required_param('composanteid', PARAM_INT);

$systemcontext = context_system::instance();

$redirecturlhometemp = new moodle_url('/my');

require_login();

$composantename = $DB->get_record('local_composantestats', array('id' => $composanteid))->name;

$configname = 'composanteuser'.$composanteid;

$userlist = get_config('local_composantestats', $configname);

$useridtab = explode(',', $userlist);

$canviewstats = false;

foreach ($useridtab as $userid) {

    if ($userid == $USER->id) {
        
        $canviewstats = true;
        break;
    }
}

if (!is_siteadmin() && !$canviewstats) {

    redirect($redirecturlhometemp);
}

$localurl = '/local/composantestats/view.php';

$PAGE->set_url($localurl);
$PAGE->set_context(context_system::instance());

$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pagetitle', 'local_composantestats', $composantename));
$PAGE->set_heading(get_string('pagetitle', 'local_composantestats', $composantename));
$output = $PAGE->get_renderer('core', 'admin');

echo $output->header();

$tab1_title = get_string('courselist', 'local_composantestats');
$tab2_title = get_string('modulelist', 'local_composantestats');
$tab3_title = get_string('top3', 'local_composantestats');
$tab4_title = get_string('graphassign', 'local_composantestats');

$tabs = [];
$tabs[] = new tabobject(1, new moodle_url($localurl, ['tab'=>1, 'composanteid' => $composanteid]), $tab1_title);
$tabs[] = new tabobject(2, new moodle_url($localurl, ['tab'=>2, 'composanteid' => $composanteid]), $tab2_title);
$tabs[] = new tabobject(3, new moodle_url($localurl, ['tab'=>3, 'composanteid' => $composanteid]), $tab3_title);
$tabs[] = new tabobject(4, new moodle_url($localurl, ['tab'=>4, 'composanteid' => $composanteid]), $tab4_title);
echo $OUTPUT->tabtree($tabs, $tab);

$descriptionsearched = "%".get_string('prefixdescription', 'local_synchrocohort')."%".$composantename."%";

$excludedcoursesid = "(". get_config('local_composantestats', 'excludedcourses'). ")";

if ($excludedcoursesid != "()") {

    $sql = "SELECT DISTINCT id FROM {course} c LEFT JOIN "
            . "(SELECT e.courseid, e.enrol FROM {enrol} e  LEFT JOIN {cohort} coh ON e.customint1 = coh.id WHERE"
            . " coh.description LIKE '$descriptionsearched' AND e.enrol LIKE 'cohort') f ON c.id = f.courseid WHERE f.enrol LIKE 'cohort' "
            . "AND c.id NOT IN $excludedcoursesid";
} else {
    
    $sql = "SELECT DISTINCT id FROM {course} c LEFT JOIN "
            . "(SELECT e.courseid, e.enrol FROM {enrol} e  LEFT JOIN {cohort} coh ON e.customint1 = coh.id WHERE"
            . " coh.description LIKE '$descriptionsearched' AND e.enrol LIKE 'cohort') f ON c.id = f.courseid WHERE f.enrol LIKE 'cohort'";
}

$listcoursesid = $DB->get_records_sql($sql);

$countcourses = count($listcoursesid);
    
if ($tab == 1) {
    // Show data for tab 1
    
    // Afficher un tableau avec la liste des cours d'une catégorie et pour chaque cours, les enseignants et le nombre d'étudiants.

    $table = new html_table();
    $table->head = array(get_string('coursename', 'local_composantestats'), get_string('teachername', 'local_composantestats'),
        get_string('numberusers', 'local_composantestats'));
    $table->colclasses = array('leftalign coursename', 'leftalign teachername', 'leftalign numberusers');
    $table->id = 'courses';
    $table->attributes['class'] = 'admintable generaltable';

    foreach ($listcoursesid as $courseid) {

        $line = array();
        
        $course = $DB->get_record('course', array('id' => $courseid->id));
        
        $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));

        $line[] = "<a href=$courseurl>$course->fullname</a>";

        $contextcourseid = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSE, 'instanceid' => $course->id))->id;
        $teacherroleid = $DB->get_record('role', array('shortname' => "teacher"))->id;
        $editingteacherroleid = $DB->get_record('role', array('shortname' => "editingteacher"))->id;
        $sqlteachers = "SELECT * FROM {role_assignments} WHERE contextid = $contextcourseid AND (roleid = $teacherroleid OR roleid = $editingteacherroleid)";
        $listteachers = $DB->get_records_sql($sqlteachers);
        $teacherlist = "";
        foreach ($listteachers as $teacherassignment) {

            $teacher = $DB->get_record('user', array('id' => $teacherassignment->userid));
            if ($teacherlist != "") {
                $teacherlist .= ", ";
            }

            $teacherlist .= "$teacher->firstname $teacher->lastname";
        }
        $line[] = $teacherlist;

        $studentroleid = $DB->get_record('role', array('shortname' => "student"))->id;
        $numberstudents = $DB->count_records('role_assignments', array('contextid' => $contextcourseid, 'roleid' => $studentroleid));
        $line[] = $numberstudents;
        $data[] = $row = new html_table_row($line);
    }
    
    if (isset($data)) {
    
        $table->data = $data;

        echo html_writer::table($table);
    }

    echo get_string('countcourses', 'local_composantestats', $countcourses);
} else if ($tab == 2) {
    // Show data for tab 2
    
    // Afficher un tableau avec la liste des modules utilisés, leur nombre d'instances et leur fréquence d'utilisation.

    $table = new html_table();
    $table->head = array(get_string('modulename', 'local_composantestats'),
        get_string('modulecount', 'local_composantestats'),
        get_string('moduleview', 'local_composantestats'),
        get_string('moduleusage', 'local_composantestats'));
    $table->colclasses = array('leftalign modulename', 'leftalign modulecount', 'leftalign moduleusage');
    $table->id = 'modules';
    $table->attributes['class'] = 'admintable generaltable';
    
    $listmodules = $DB->get_records('modules', array('visible' => 1));
    
    foreach ($listmodules as $module) {
        
        $modulestats = $DB->get_record('local_cronstats', array('composanteid' => $composanteid, 'modid' => $module->id));
        
        $line = array();
        
        if ($modulestats->usagecount != 0) {

            $line[] = get_string('modulename', 'mod_'.$module->name);
            $line[] = $modulestats->usagecount;
            $line[] = $modulestats->statsviewed;
            if ($modulestats->statsused != null  && $modulestats->statsused != "") {
                
                $line[] = $modulestats->statsused;
            } else {
                
                $line[] = "N/A";
            }
        }
        
        $data[] = $row = new html_table_row($line);
    }
    
    $table->data = $data;

    echo html_writer::table($table);
} else if ($tab == 3) {
    
    // Show data for tab 3
    
    // Afficher un tableau avec la liste des 3 modules les plus utilisés, leur nombre d'instances ou leur fréquence d'utilisation suivant le type.

    $tableused = new html_table();
    $tableused->head = array(get_string('modulename', 'local_composantestats'),
        get_string('moduleusage', 'local_composantestats'));
    $tableused->colclasses = array('leftalign modulename', 'leftalign statsused');
    $tableused->id = 'modules';
    $tableused->attributes['class'] = 'admintable generaltable';
    
    $sqlmodulesused = "SELECT * FROM {local_cronstats} WHERE composanteid = $composanteid AND statsused != 0 ORDER BY statsused DESC LIMIT 3";
    
    $listmodulesused = $DB->get_records_sql($sqlmodulesused);
    
    foreach ($listmodulesused as $statsmoduleused) {
        
        $line = array();

        $module = $DB->get_record('modules', array('id' => $statsmoduleused->modid));
        
        $line[] = get_string('modulename', 'mod_'.$module->name);    
        $line[] = $statsmoduleused->statsused;
        
        $data[] = $row = new html_table_row($line);
    }
    
    if (isset($data)) {
    
        $tableused->data = $data;

        echo html_writer::table($tableused);
    }
    
    $tableviewed = new html_table();
    $tableviewed->head = array(get_string('modulename', 'local_composantestats'),
        get_string('moduleview', 'local_composantestats'));
    $tableviewed->colclasses = array('leftalign modulename', 'leftalign statsused');
    $tableviewed->id = 'modules';
    $tableviewed->attributes['class'] = 'admintable generaltable';
    
    $sqlmodulesviewed = "SELECT * FROM {local_cronstats} WHERE composanteid = $composanteid AND statsused is null"
            . " ORDER BY statsviewed DESC LIMIT 3";
    
    $listmodulesviewed = $DB->get_records_sql($sqlmodulesviewed);
    
    foreach ($listmodulesviewed as $statsmoduleviewed) {
        
        $line = array();

        $module = $DB->get_record('modules', array('id' => $statsmoduleviewed->modid));
        
        $line[] = get_string('modulename', 'mod_'.$module->name);    
        $line[] = $statsmoduleviewed->statsviewed;
        
        $data2[] = $row = new html_table_row($line);
    }
    
    $tableviewed->data = $data2;

    echo html_writer::table($tableviewed);
    
} else if ($tab == 4) {
    
    $coursesstring = "(";
    
    foreach ($listcoursesid as $courseid) {
 
        $coursesstring.= "$courseid->id,";
    }
    
    $finalcoursesstring = substr_replace($coursesstring, ")", -1);
    
    if ($finalcoursesstring != ")") {
    
        // Extraire tout les devoirs dans les cours de la composante.

        $listassignsql = "SELECT * FROM {assign} WHERE course IN $finalcoursesstring";

        $listassign = $DB->get_records_sql($listassignsql);

        // Extraire tous les quiz dans les cours de la composante.

        $listquizsql = "SELECT * FROM {quiz} WHERE course IN $finalcoursesstring";

        $listquiz = $DB->get_records_sql($listquizsql);

        if (isset($listassign)) {

            // Récupérer toutes les infos sur les dépôts de devoir.

            $assignstring = "(";

            foreach ($listassign as $assign) {

                $assignstring.= $assign->id.",";
            }

            $finalassignstring = substr_replace($assignstring, ")", -1);

            $weekstart = 0;

            $starttimestamp = strtotime(get_config('local_composantestats', 'startdategraph'));

            if ($finalassignstring != ")") {

                $labelassigns = array();
                $seriesassigns = array();

                for ($week = 0; $week < 52; $week++) {

                    $nextweek = $week + 1;

                    $timestampassignstart = $starttimestamp + ($week*(24*7*3600));
                    $timestampassignend = $timestampassignstart + (24*7*3600);

                    $sqlassign = "SELECT COUNT(*) FROM {assign_submission} WHERE assignment IN $finalassignstring "
                        . "AND timemodified >= $timestampassignstart AND timemodified < $timestampassignend";
                    $countassignsubmission = $DB->count_records_sql($sqlassign);
                    
                    // Calcul de l'affichage de la semaine affichée en partant de la date du 1er lundi de septembre.
                    // Todo : Faire plutôt un extract de la semaine à partir de la date du timestamp.
                    
                    $nextdisplayedweek = (($week + 36) % 52);

                    if ($nextdisplayedweek < 36) {
                        
                        $nextdisplayedweek + 1;
                    }
                    $labelassigns[] = get_string('week', 'local_composantestats', $nextdisplayedweek);
                    $seriesassigns[] = $countassignsubmission;
                }


                $serieassign = new core\chart_series(get_string('assign', 'local_composantestats'), $seriesassigns);

                $chartassign = new core\chart_bar();
                $chartassign->add_series($serieassign);
                $chartassign->set_labels($labelassigns);
                echo $OUTPUT->render($chartassign);
            } else {
            
            echo get_string('noassign', 'local_composantestats');
        }
        } else {
            
            echo get_string('noassign', 'local_composantestats');
        }

        if (isset($listquiz)) {

            // Récupérer toutes les infos sur les tentatives de quiz.

            $quizstring = "(";

            foreach ($listquiz as $quiz) {

                $quizstring.= $quiz->id.",";
            }

            $finalquizstring = substr_replace($quizstring, ")", -1);

            if ($finalquizstring != ")") {

                $labelquiz = array();
                $seriesquiz = array();

                for ($week = 0; $week < 52; $week++) {

                    $nextweek = $week + 1;

                    $timestampquizstart = $starttimestamp + ($week*(24*7*3600));
                    $timestampquizend = $timestampquizstart + (24*7*3600);

                    $sqlquiz = "SELECT COUNT(*) FROM {quiz_attempts} WHERE quiz IN $finalquizstring "
                        . "AND timefinish >= $timestampquizstart AND timefinish < $timestampquizend";
                    $countquizfinished = $DB->count_records_sql($sqlquiz);
                    
                    // Calcul de l'affichage de la semaine affichée en partant de la date du 1er lundi de septembre.
                    // Todo : Faire plutôt un extract de la semaine à partir de la date du timestamp.
                    
                    $nextdisplayedweek = (($week + 36) % 52);

                    if ($nextdisplayedweek < 36) {
                        
                        $nextdisplayedweek + 1;
                    }

                    $labelquiz[] = get_string('week', 'local_composantestats', $nextdisplayedweek);
                    $seriesquiz[] = $countquizfinished;
                }



                $seriequiz = new core\chart_series(get_string('quiz', 'local_composantestats'), $seriesquiz);

                $chartquiz = new core\chart_bar();
                $chartquiz->add_series($seriequiz);
                $chartquiz->set_labels($labelquiz);
                echo $OUTPUT->render($chartquiz);
            } else {
            
            echo get_string('noquiz', 'local_composantestats');
        }
        } else {
            
            echo get_string('noquiz', 'local_composantestats');
        }

    }
}

echo $output->footer();