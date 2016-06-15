<?php


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

global $DB;

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

$context = context_module::instance($cm->id);

// Output starts here.
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
if ($pa->intro) {
    echo $OUTPUT->box(format_module_intro('pa', $pa, $cm->id), 'generalbox mod_introbox', 'paintro');
}

echo $OUTPUT->heading('Test cases');

    $id = required_param('id', PARAM_INT);
    $sql="SELECT * FROM mdl_pa WHERE id=$pa->id";
    $data=$DB->get_record_sql($sql,null);
    echo"<br>";

    $table=new html_table();

    //table stucture
    $row=new html_table_row();
    $row->cells=array(new html_table_cell("<b>Test case</b>"),new html_table_cell("<b>Input</b>"),new html_table_cell("<b>Output</b>"),new html_table_cell("<b>Status</b>"),new html_table_cell("<b>Hidden</b>"));
    $table->data[]=$row;

    //test case 1
    $row=new html_table_row();
    if($data->use1==0)
        $u="Not used";
    else
        $u="Used";
    if($data->visible1==0)
        $v="Not hidden";
    else
        $v="Hidden";
    $row->cells=array(new html_table_cell("Test case 1"),new html_table_cell($data->input1),new html_table_cell($data->output1),new html_table_cell($u),new html_table_cell($v));
    if(has_capability('mod/pa:testcase',$context))
        $table->data[]=$row;
    else
        if($data->visible1==0)
            $table->data[]=$row;

    //test case 2
    $row=new html_table_row();
    if($data->use2==0)
        $u="Not used";
    else
        $u="Used";
    if($data->visible2==0)
        $v="Not hidden";
    else
        $v="Hidden";
    $row->cells=array(new html_table_cell("Test case 2"),new html_table_cell($data->input2),new html_table_cell($data->output2),new html_table_cell($u),new html_table_cell($v));
    if(has_capability('mod/pa:testcase',$context))
        $table->data[]=$row;
    else
        if($data->visible2==0)
            $table->data[]=$row;

    //test case 3
    $row=new html_table_row();
    if($data->use3==0)
        $u="Not used";
    else
        $u="Used";
    if($data->visible3==0)
        $v="Not hidden";
    else
        $v="Hidden";
    $row->cells=array(new html_table_cell("Test case 3"),new html_table_cell($data->input3),new html_table_cell($data->output3),new html_table_cell($u),new html_table_cell($v));
    if(has_capability('mod/pa:testcase',$context))
        $table->data[]=$row;
    else
        if($data->visible3==0)
            $table->data[]=$row;

    echo html_writer::table($table);

$urlparams = array('id' => $id);
$url = new moodle_url('/mod/pa/submission.php', $urlparams);
$backlink = $OUTPUT->action_link($url, 'Add New Submission');

echo $OUTPUT->heading('Submission');
echo $backlink;

// Finish the page.
echo $OUTPUT->footer();