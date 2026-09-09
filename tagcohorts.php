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
 *
 * @package    local_composantestats
 * @author     Laurent GUILLET <laurent.guillet@univ-eiffel.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot . '/local/composantestats/tagcohorts_form.php');

require_admin();

$systemcontext = context_system::instance();

$redirecturlhometemp = new moodle_url('/my');

if (isguestuser()) {

    redirect($redirecturlhometemp);
}

require_login();

$PAGE->set_context(context_system::instance());

// Instantiate simplehtml_form.
$mform = new tagcohorts_form();

$redirecturlhome = new moodle_url('/my');

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {

    redirect($redirecturlhome);
} else if ($fromform = $mform->get_data()) {
    
    $pluginname = "composantestats";
    
    $content = $mform->get_file_content('teachersfile');
    $importid = csv_import_reader::get_new_iid($pluginname);
    $cir = new csv_import_reader($importid, $pluginname);
    
    $readcount = $cir->load_csv_content($content, 'utf-8', 'semicolon');
    unset($content);
    
    $cir->init();
    
    $stringin = "(";
    
    while ($line = $cir->next()) {
        
        $stringin .= "'".$line[0]."',";
    }
    
    $finalstringin = substr_replace($stringin, ")", -1);
    
    $sql = "select DISTINCT mc.id 
        from mdl_cohort mc 
        join mdl_enrol me on (me.enrol='cohort' and me.customint1=mc.id) 
        join mdl_course mco on (me.courseid=mco.id)
        join mdl_course_categories mcc on (mcc.id=mco.category)
        join mdl_context mcx on (mco.id=mcx.instanceid AND mcx.contextlevel=50)
        Join mdl_role_assignments mra on (mra.contextid=mcx.id)
        Join mdl_user mu on ( mu.id=mra.userid and mra.roleid=3)
        where mu.email IN $finalstringin";
    
    $listcohorts = $DB->get_records_sql($sql);
    print_object($listcohorts);

    
} else {

    $PAGE->set_url('/local/composantestats/tagcohorts.php');
    $PAGE->set_pagelayout('report');
    $PAGE->set_context(context_system::instance());

    $title = get_string('pluginname', 'local_composantestats');
    $PAGE->set_title($title);
    $PAGE->set_heading($title);
    $PAGE->navbar->add(get_string('pluginname', 'local_composantestats'), new moodle_url('/local/composantestats/tagcohorts.php'));

    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
