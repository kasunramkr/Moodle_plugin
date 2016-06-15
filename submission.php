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
                    <option value=\"4\">Python (python 2.6.4)</option>
                    <option value=\"11\">C (gcc-4.3.4)</option>
                    <option value=\"27\">C# (mono-2.8)</option>
                    <option value=\"44\">C++0x (gcc-4.5.1)</option>
                    <option value=\"10\">Java (sun-jdk-1.6.0.17)</option>
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

    $sql="SELECT * FROM mdl_pa WHERE id=$pa->id";
    $temp=$DB->get_record_sql($sql,null);
    $use1=$temp->use1;
    $use2=$temp->use2;
    $use3=$temp->use3;
    $input1=$temp->input1;
    $input2=$temp->input2;
    $input3=$temp->input3;
    $output1=$temp->output1;
    $output2=$temp->output2;
    $output3=$temp->output3;
    $output1=$temp->output1;
    $mark1=$temp->mark1;
    $mark2=$temp->mark2;
    $mark3=$temp->mark3;

    $marks=0;

    if($use1){
        error_reporting(0);

        $user = 'f0da1ced660fc662c70bbd0d23fd1ea2';
        $pass = '1ee815729951fbcd9ae80ab6446a464a';
        $code = $source;
        $input = $input1;
        $lang=$language;
        $run = true;
        $private = false;

        $subStatus = array(
            0 => 'Success',
            1 => 'Compiled',
            3 => 'Running',
            11 => 'Compilation Error',
            12 => 'Runtime Error',
            13 => 'Time limit exceeded',
            15 => 'Success',
            17 => 'memory limit exceeded',
            19 => 'illegal system call',
            20 => 'internal error'
        );

        $error1 = array(
            'status' => 'error',
            'output' => 'Something went wrong :( 1'
        );

        $error2 = array(
            'status' => 'error',
            'output' => 'Something went wrong :( 2'
        );
        $error3 = array(
            'status' => 'error',
            'output' => 'Something went wrong :( 3'
        );

        $lang = isset( $_POST['lang'] ) ? intval( $_POST['lang'] ) : 1;
        //$input = trim( $_POST['input'] );
        $code = trim(stripslashes( $_POST['source'] ));

        $client = new SoapClient( "http://18a9d85d.compilers.sphere-engine.com/api/1/service.wsdl" );

        //create new submission
        $result = $client->createSubmission( $user, $pass, $code, $lang, $input, $run, $private );

        //if submission is OK, get the status
        if ( $result['error'] == 'OK' ) {
            $status = $client->getSubmissionStatus( $user, $pass, $result['link'] );
            if ( $status['error'] == 'OK' ) {

                //check if the status is 0, otherwise getSubmissionStatus again
                while ( $status['status'] != 0 ) {
                    sleep( 3 ); //sleep 3 seconds
                    $status = $client->getSubmissionStatus( $user, $pass, $result['link'] );
                }

                //finally get the submission results
                $details = $client->getSubmissionDetails( $user, $pass, $result['link'], true, true, true, true, true );
                if ( $details['error'] == 'OK' ) {
                    //print_r( $details );
                    if ( $details['status'] < 0 ) {
                        $status = 'waiting for compilation';
                    } else {
                        $status = $subStatus[$details['status']];
                    }

                    $data = array(
                        'status' => 'success',
                        'meta' => "Status: $status | Memory: {$details['memory']} | Returned value: {$details['status']} | Time: {$details['time']}s",
                        'output' => htmlspecialchars( $details['output'] ),
                        'raw' => $details
                    );

                    if( $details['cmpinfo'] ) {
                        $data['cmpinfo'] = $details['cmpinfo'];
                    }

                    //echo json_encode( $data );
                } else {
                    // echo json_encode( $error1 );
                }
            } else {
                // echo json_encode( $error2 );
            }
        } else {
            $error3 = array(
                'status' => 'error',
                'output' => $result['error']
            );

            //  echo json_encode( $error3 );
        }
        echo "<b>Test Case 1</b><br>";
        if(strcmp(trim($output1),trim($data['output']))==0) {
            echo "<b>Marks for test case 1: </b>" . $mark1."<br><br>";
            $marks=$marks+$mark1;
        }
        else
            echo "Marks for test case 1: 0<br><br>";
        echo "<b>Status : </b>".$data['status']."<br><br>";
        echo "<b>Output : </b>".$data['output']."<br><br>";
        echo "<b>Details : </b>".$data['meta']."<br><br>";
        echo "<b>Error : </b>".$data['cmpinfo']."<br><br>";

    }

    if($use2){
        error_reporting(0);

        $user = 'f0da1ced660fc662c70bbd0d23fd1ea2';
        $pass = '1ee815729951fbcd9ae80ab6446a464a';
        $code = $source;
        $input = $input2;
        $lang=$language;
        $run = true;
        $private = false;

        $subStatus = array(
            0 => 'Success',
            1 => 'Compiled',
            3 => 'Running',
            11 => 'Compilation Error',
            12 => 'Runtime Error',
            13 => 'Time limit exceeded',
            15 => 'Success',
            17 => 'memory limit exceeded',
            19 => 'illegal system call',
            20 => 'internal error'
        );

        $error1 = array(
            'status' => 'error',
            'output' => 'Something went wrong :( 1'
        );

        $error2 = array(
            'status' => 'error',
            'output' => 'Something went wrong :( 2'
        );
        $error3 = array(
            'status' => 'error',
            'output' => 'Something went wrong :( 3'
        );

        $lang = isset( $_POST['lang'] ) ? intval( $_POST['lang'] ) : 1;
        //$input = trim( $_POST['input'] );
        $code = trim(stripslashes( $_POST['source'] ));

        $client = new SoapClient( "http://18a9d85d.compilers.sphere-engine.com/api/1/service.wsdl" );

        //create new submission
        $result = $client->createSubmission( $user, $pass, $code, $lang, $input, $run, $private );

        //if submission is OK, get the status
        if ( $result['error'] == 'OK' ) {
            $status = $client->getSubmissionStatus( $user, $pass, $result['link'] );
            if ( $status['error'] == 'OK' ) {

                //check if the status is 0, otherwise getSubmissionStatus again
                while ( $status['status'] != 0 ) {
                    sleep( 3 ); //sleep 3 seconds
                    $status = $client->getSubmissionStatus( $user, $pass, $result['link'] );
                }

                //finally get the submission results
                $details = $client->getSubmissionDetails( $user, $pass, $result['link'], true, true, true, true, true );
                if ( $details['error'] == 'OK' ) {
                    //print_r( $details );
                    if ( $details['status'] < 0 ) {
                        $status = 'waiting for compilation';
                    } else {
                        $status = $subStatus[$details['status']];
                    }

                    $data = array(
                        'status' => 'success',
                        'meta' => "Status: $status | Memory: {$details['memory']} | Returned value: {$details['status']} | Time: {$details['time']}s",
                        'output' => htmlspecialchars( $details['output'] ),
                        'raw' => $details
                    );

                    if( $details['cmpinfo'] ) {
                        $data['cmpinfo'] = $details['cmpinfo'];
                    }

                    //echo json_encode( $data );
                } else {
                    // echo json_encode( $error1 );
                }
            } else {
                // echo json_encode( $error2 );
            }
        } else {
            $error3 = array(
                'status' => 'error',
                'output' => $result['error']
            );

            //  echo json_encode( $error3 );
        }
        echo "<b>Test Case 2</b><br>";
        if(strcmp(trim($output2),trim($data['output']))==0){
            echo "<b>Marks for test case 2: </b>".$mark2."<br><br>";
        $marks=$marks+$mark2;
        }
        else
            echo "Marks for test case 2: 0<br><br>";
        echo "<b>Status : </b>".$data['status']."<br><br>";
        echo "<b>Output : </b>".$data['output']."<br><br>";
        echo "<b>Details : </b>".$data['meta']."<br><br>";
        echo "<b>Error : </b>".$data['cmpinfo']."<br><br>";

    }

    if($use3){
        error_reporting(0);

        $user = 'f0da1ced660fc662c70bbd0d23fd1ea2';
        $pass = '1ee815729951fbcd9ae80ab6446a464a';
        $code = $source;
        $input = $input3;
        $lang=$language;
        $run = true;
        $private = false;

        $subStatus = array(
            0 => 'Success',
            1 => 'Compiled',
            3 => 'Running',
            11 => 'Compilation Error',
            12 => 'Runtime Error',
            13 => 'Time limit exceeded',
            15 => 'Success',
            17 => 'memory limit exceeded',
            19 => 'illegal system call',
            20 => 'internal error'
        );

        $error1 = array(
            'status' => 'error',
            'output' => 'Something went wrong :( 1'
        );

        $error2 = array(
            'status' => 'error',
            'output' => 'Something went wrong :( 2'
        );
        $error3 = array(
            'status' => 'error',
            'output' => 'Something went wrong :( 3'
        );

        $lang = isset( $_POST['lang'] ) ? intval( $_POST['lang'] ) : 1;
        //$input = trim( $_POST['input'] );
        $code = trim(stripslashes( $_POST['source'] ));

        $client = new SoapClient( "http://18a9d85d.compilers.sphere-engine.com/api/1/service.wsdl" );

        //create new submission
        $result = $client->createSubmission( $user, $pass, $code, $lang, $input, $run, $private );

        //if submission is OK, get the status
        if ( $result['error'] == 'OK' ) {
            $status = $client->getSubmissionStatus( $user, $pass, $result['link'] );
            if ( $status['error'] == 'OK' ) {

                //check if the status is 0, otherwise getSubmissionStatus again
                while ( $status['status'] != 0 ) {
                    sleep( 3 ); //sleep 3 seconds
                    $status = $client->getSubmissionStatus( $user, $pass, $result['link'] );
                }

                //finally get the submission results
                $details = $client->getSubmissionDetails( $user, $pass, $result['link'], true, true, true, true, true );
                if ( $details['error'] == 'OK' ) {
                    //print_r( $details );
                    if ( $details['status'] < 0 ) {
                        $status = 'waiting for compilation';
                    } else {
                        $status = $subStatus[$details['status']];
                    }

                    $data = array(
                        'status' => 'success',
                        'meta' => "Status: $status | Memory: {$details['memory']} | Returned value: {$details['status']} | Time: {$details['time']}s",
                        'output' => htmlspecialchars( $details['output'] ),
                        'raw' => $details
                    );

                    if( $details['cmpinfo'] ) {
                        $data['cmpinfo'] = $details['cmpinfo'];
                    }

                   // echo json_encode( $data );
                } else {
                     //echo json_encode( $error1 );
                }
            } else {
                //echo json_encode( $error2 );
            }
        } else {
            $error3 = array(
                'status' => 'error',
                'output' => $result['error']
            );

             // echo json_encode( $error3 );
        }
        echo "<b>Test Case 3</b><br>";
        if(strcmp(trim($output3),trim($data['output']))==0){
            echo "<b>Marks for test case 3 : </b>".$mark3."<br><br>";
            $marks=$marks+$mark3;
        }
        else
            echo "Marks for test case 3: 0<br><br>";
        echo "<b>Status : </b>".$data['status']."<br><br>";
        echo "<b>Output : </b>".$data['output']."<br><br>";
        echo "<b>Details : </b>".$data['meta']."<br><br>";
        echo "<b>Error : </b>".$data['cmpinfo']."<br><br>";

    }
    echo "<b>Total Marks : </b>".$marks;
    $sql="INSERT INTO mdl_pa_submission(assignment_id,datesubmitted,source,language,user_id,grade) VALUES ($assignment_id,$datesubmitted,'$source',$language,$user_id,$marks)";
    $DB->execute($sql, null);
}



$urlparams = array('id' => $id);
$url = new moodle_url('/mod/pa/view_submission.php', $urlparams);
$backlink = $OUTPUT->action_link($url, 'View all Submissions');
echo $OUTPUT->heading('View Submission');
echo $backlink;

// Finish the page.
echo $OUTPUT->footer();