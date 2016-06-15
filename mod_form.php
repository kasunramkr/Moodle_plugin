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

        $mform->addElement('checkbox','use1','Use test case');
        $mform->addElement('checkbox','visible1','Hide for student');
        $mform->addElement('textarea', 'input1', 'Input 1');
        $mform->addElement('textarea', 'output1', 'Output 1');
        $mform->addElement('textarea', 'mark1', 'Marks');

        $mform->setType('mark1',PARAM_INT);
        $mform->disabledIf('input1','use1');
        $mform->disabledIf('output1','use1');
        $mform->disabledIf('visible1','use1');
        $mform->disabledIf('mark1','use1');

        $mform->addElement('checkbox','use2','Use test case');
        $mform->addElement('checkbox','visible2','Hide for student');
        $mform->addElement('textarea', 'input2', 'Input 2');
        $mform->addElement('textarea', 'output2', 'Output 2');
        $mform->addElement('textarea', 'mark2', 'Marks');

        $mform->setType('mark2',PARAM_INT);
        $mform->disabledIf('input2','use2');
        $mform->disabledIf('output2','use2');
        $mform->disabledIf('visible2','use2');
        $mform->disabledIf('mark2','use2');

        $mform->addElement('checkbox','use3','Use test case');
        $mform->addElement('checkbox','visible3','Hide for student');
        $mform->addElement('textarea', 'input3', 'Input 3');
        $mform->addElement('textarea', 'output3', 'Output 3');
        $mform->addElement('textarea', 'mark3', 'Marks');

        $mform->setType('mark3',PARAM_INT);
        $mform->disabledIf('input3','use3');
        $mform->disabledIf('output3','use3');
        $mform->disabledIf('visible3','use3');
        $mform->disabledIf('mark3','use3');

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}
