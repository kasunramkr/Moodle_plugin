<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 15-Jun-16
 * Time: 16:08
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

global $DB;
global $USER;

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

$PAGE->set_url('/mod/pa/submission.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pa->name));
$PAGE->set_heading(format_string($course->fullname));

$context = context_module::instance($cm->id);

// Output starts here.
echo $OUTPUT->header();

echo "<form method='post'>";

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

echo "<textarea name='source' style='font-size:15pt;height:500px;width:700px;'></textarea><br/>";
echo "<input name='submit' type='submit' value='Add Submition'></input>";
echo "</form>";

if(isset($_POST['submit'])){
    $assignment_id=$pa->id;
    $datesubmitted=time();
    $source=($_POST['source']);
    $language=($_POST['lang']);
    $user_id=$USER->id;

    $sql="INSERT INTO mdl_pa_submission(assignment_id,datesubmitted,source,language,user_id) VALUES ($assignment_id,$datesubmitted,'$source',$language,$user_id)";
    $DB->execute($sql, null);

}

$urlparams = array('id' => $id);
$url = new moodle_url('/mod/pa/view_submission.php', $urlparams);
$backlink = $OUTPUT->action_link($url, 'View all Submissions');
echo $OUTPUT->heading('View Submission');
echo $backlink;

// Finish the page.
echo $OUTPUT->footer();