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
 * The main pa configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_pa
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 *
 * @package    mod_pa
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_pa_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('paname', 'pa'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'paname', 'pa');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of pa settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.

        $mform->addElement('header', 'pafieldset', get_string('pafieldset', 'pa'));

        $mform->addElement('text', 'input1', 'Input 1');
        $mform->addElement('text', 'output1', 'Output 1');

        $mform->addElement('text', 'input2', 'Input 2');
        $mform->addElement('text', 'output2', 'Output 2');

        $mform->addElement('text', 'input3', 'Input 3');
        $mform->addElement('text', 'output3', 'Output 3');

//      $mform->addElement('static', 'label1', 'Test Case 1 <br/>Input', "<textarea name='input1' style='font-size:15pt;height:130px;width:400px;'></textarea>");
//		$mform->addElement('static', 'label1', 'Output', "<textarea name='output1' style='font-size:15pt;height:130px;width:400px;'></textarea>");

//		$mform->addElement('static', 'label1', 'Test Case 2 <br/>Input', "<textarea name='input2' style='font-size:15pt;height:130px;width:400px;'></textarea>");
//		$mform->addElement('static', 'label1', 'Output', "<textarea name='output2' style='font-size:15pt;height:130px;width:400px;'></textarea>");

//		$mform->addElement('static', 'label1', 'Test Case 3 <br/>Input', "<textarea name='input3' style='font-size:15pt;height:130px;width:400px;'></textarea>");
//		$mform->addElement('static', 'label1', 'Output', "<textarea name='output3' style='font-size:15pt;height:130px;width:400px;'></textarea>");

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}
