<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 15-Jun-16
 * Time: 17:25
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

$PAGE->set_url('/mod/pa/view_submission.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pa->name));
$PAGE->set_heading(format_string($course->fullname));

$context = context_module::instance($cm->id);

// Output starts here.
echo $OUTPUT->header();

if(has_capability('mod/pa:testcase',$context))
    $sql="SELECT * FROM mdl_pa_submission WHERE assignment_id=$pa->id";
else
    $sql="SELECT * FROM mdl_pa_submission WHERE user_id=$USER->id AND assignment_id=$pa->id";

$data=$DB->get_records_sql($sql,null);

$table=new html_table();
$row=new html_table_row();
$row->cells=array(new html_table_cell("<b>User id</b>"),new html_table_cell("<b>Language</b>"),new html_table_cell("<b>Source code</b>"),new html_table_cell("<b>Grade</b>"),new html_table_cell("<b>Submission date</b>"));
$table->data[]=$row;

foreach ($data as $key => $usr){
    $row=new html_table_row();
    $datesubmitted = date('Y-m-d H:i:s a', $usr->datesubmitted);

    $lang=$usr->language;
    $data2=$DB->get_record_sql("SELECT language_name FROM mdl_pa_languages_details WHERE language_id=$lang",null);

    $row->cells=array(new html_table_cell($usr->user_id),new html_table_cell($data2->language_name),new html_table_cell($usr->source),new html_table_cell($usr->grade),new html_table_cell($datesubmitted));
    $table->data[]=$row;
}

echo html_writer::table($table);

// Finish the page.
echo $OUTPUT->footer();