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
 * Prints a particular instance of pa
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_pa
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace pa with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... pa instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('pa', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $pa  = $DB->get_record('pa', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $pa  = $DB->get_record('pa', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $pa->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('pa', $pa->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_pa\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $pa);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/pa/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pa->name));
$PAGE->set_heading(format_string($course->fullname));

// Output starts here.
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
if ($pa->intro) {
    echo $OUTPUT->box(format_module_intro('pa', $pa, $cm->id), 'generalbox mod_introbox', 'paintro');
}

echo $OUTPUT->heading('Submition');

echo "  Select Language  ";
echo "<select name=\"lang\" id=\"lang\" class=\"span6\">
                    <option value=\"11\">C (gcc-4.3.4)</option>
                    <option value=\"27\">C# (mono-2.8)</option>
                    <option value=\"44\">C++0x (gcc-4.5.1)</option>
                    <option value=\"10\">Java (sun-jdk-1.6.0.17)</option>
                    <option value=\"4\">Python (python 2.6.4)</option>
                    <option value=\"116\">Python 3 (python-3.1.2)</option>
                    <option value=\"40\">SQL (sqlite3-3.7.3)</option>
                </select>";

echo "<br>";
echo "<br>";

echo "<input type=\"file\" name=\"fileToUpload\" id=\"fileToUpload\">";
echo "<br>";
echo "<br>";

echo "<textarea name='source' style='font-size:15pt;height:500px;width:700px;'></textarea><br/>";
echo "<button type='button'>Submit</button>";

// Finish the page.
echo $OUTPUT->footer();