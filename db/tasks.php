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
 * Lists of scheduled tasks for the plugin.
 *
 * @package     local_composantestats
 * @copyright   2024 Laurent Guillet <laurent.guillet@univ-eiffel.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined('MOODLE_INTERNAL') || die();
$tasks = [
    [
        'classname' => '\local_composantestats\task\checkcomposantenames',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '10',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
    
    [
        'classname' => '\local_composantestats\task\createcronstats',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '3',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];
