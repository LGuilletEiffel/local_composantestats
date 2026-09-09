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
 * Plugin strings are defined here.
 *
 * @package     local_composantestats
 * @category    string
 * @copyright   2024 Laurent Guillet <laurent.guillet@univ-eiffel.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Component statistics';
$string['privacy:metadata'] = 'The Composante statistics plugin does not record personal data'
        . ' outside of standard Moodle systems.';
$string['checkcomposantenames'] = 'Scheduled task to update the list of composante names';
$string['createcronstats'] = 'Scheduled task to create composante stats';
$string['coursename'] = 'Course name';
$string['teachername'] = 'Teacher name';
$string['numberusers'] = 'Number of students';
$string['modulename'] = 'Module name';
$string['modulecount'] = 'Number of module instances';
$string['moduleview'] = 'Views of module';
$string['moduleusage'] = 'Usage of module';
$string['courselist'] = 'List of courses';
$string['modulelist'] = 'List of modules used';
$string['top3'] = 'Top 3 of modules usage';
$string['graphassign'] = 'Graph of assignment submission';
$string['pagetitle'] = 'Statistics of component {$a}';
$string['composanteuser'] = 'Manager of component {$a}';
$string['composantestats:canuseplugin'] = 'Can use Component statistics plugin';
$string['countcourses'] = 'Number of courses : {$a}';
$string['startdategraph'] = 'Start date for graphics';
$string['dateformatexplanation'] = 'Dates use the dd-mm-YYYY format';
$string['week'] = 'Week {$a}';
$string['assign'] = 'Assign';
$string['quiz'] = 'Quiz';
$string['noassign'] = 'You don\'t have assignments in your courses so no graphics will be displayed<br>';
$string['noquiz'] = 'You don\'t have quizzes in your courses so no graphics will be displayed<br>';
$string['excludedcourses'] = 'Excluded courses separated by comma';
