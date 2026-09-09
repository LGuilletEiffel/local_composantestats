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

// Cette fonction est censée récupérer la liste des utilisateurs devant rendre un devoir (stats 1/A/n°1)

/**
     * Retrieves the list of students to be graded for the assignment.
     *
     * @param int $assignid the assign instance id
     * @param int $groupid the current group id
     * @param string $filter search string to filter the results.
     * @param int $skip Number of records to skip
     * @param int $limit Maximum number of records to return
     * @param bool $onlyids Only return user ids.
     * @param bool $includeenrolments Return courses where the user is enrolled.
     * @param bool $tablesort Apply current user table sorting params from the grading table.
     * @return array of warnings and status result
     * @since Moodle 3.1
     * @throws moodle_exception
     */
    list_participants($assignid, $groupid, $filter, $skip,
            $limit, $onlyids, $includeenrolments, $tablesort);