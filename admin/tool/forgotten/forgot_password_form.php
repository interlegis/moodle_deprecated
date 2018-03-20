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
 * Reset forgotten password form definition.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2006 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';
require_once $CFG->libdir.'/datalib.php';

class login_forgot_password_form extends moodleform {

    function definition() {
        $mform    = $this->_form;
        $mform->setDisableShortforms(true);

        $mform->addElement('header', 'searchbyusername', get_string('searchbyusername'), '');

        $mform->addElement('text', 'username', get_string('username'));
        $mform->setType('username', PARAM_RAW);

        $submitlabel = get_string('search');
        $mform->addElement('submit', 'submitbuttonusername', $submitlabel);

        $mform->addElement('header', 'searchbyemail', get_string('searchbyemail'), '');

        $mform->addElement('text', 'email', get_string('email'));
        $mform->setType('email', PARAM_RAW);

        $submitlabel = get_string('search');
        $mform->addElement('submit', 'submitbuttonemail', $submitlabel);

        $mform->addElement('header', 'searchbycpf', get_string('searchbycpf', 'tool_forgotten'), '');

        $mform->addElement('text', 'cpf', get_string('cpf', 'tool_forgotten'));
        $mform->setType('cpf', PARAM_RAW);

        $mform->addElement('submit', 'submitbuttoncpf', $submitlabel);

    }

    function validation($data, $files) {
        global $CFG, $DB;

        $errors = parent::validation($data, $files);

        if ((empty($data['username']) and empty($data['email']) and !empty($data['cpf'])) or
            (empty($data['username']) and !empty($data['email']) and empty($data['cpf'])) or 
            (!empty($data['username']) and empty($data['email']) and empty($data['cpf']))) 
        {
            if (!empty($data['email'])) {
                if (!validate_email($data['email'])) {
                    $errors['email'] = get_string('invalidemail');
                } else if ($DB->count_records('user', array('email'=>$data['email'])) > 1) {
                    $errors['email'] = get_string('forgottenduplicate');
                } else {
                    if ($user = get_complete_user_data('email', $data['email'])) {
                        if (empty($user->confirmed)) {
                            $errors['email'] = get_string('confirmednot');
                        }
                    }
                    if (!$user and empty($CFG->protectusernames)) {
                        $errors['email'] = get_string('emailnotfound');
                    }
                }
            } else if (!empty($data['username'])) {
                if ($user = get_complete_user_data('username', $data['username'])) {
                    if (empty($user->confirmed)) {
                        $errors['email'] = get_string('confirmednot');
                    }
                }
                if (!$user and empty($CFG->protectusernames)) {
                    $errors['username'] = get_string('usernamenotfound');
                }
            } else {
                if (!$campo = $DB->get_record('user_info_field', array('shortname'=>'cpf'))) {
                    $errors['cpf'] = get_string('cpffieldnotfound', 'tool_forgotten');
                } else {
                    $params = array('guestid'=>$CFG->siteguest, 'mnethostid'=>$CFG->mnet_localhost_id, 'fieldid'=>$campo->id, 'cpf'=>$data['cpf']);
                    $select = 'id <> :guestid AND deleted = 0 AND mnethostid = :mnethostid AND id IN (SELECT userid FROM {user_info_data} WHERE fieldid=:fieldid AND data = :cpf)';
                    if (!$users = $DB->get_records_select('user', $select, $params)) {
                        if (empty($CFG->protectusernames)) {
                            $errors['cpf'] = get_string('usercpfnotfound', 'tool_forgotten');
                        }
                    } else {
                        if (count($users) > 1) {
                            $errors['cpf'] = get_string('forgottenduplicate', 'tool_forgotten');
                        } else {
                            foreach ($users as $user) {
                                if (empty($user->confirmed)) {
                                    $errors['cpf'] = get_string('confirmednot');
                                }
                            } 
                        }
                    }
                }
            }
        } else {
            $errors['username'] = get_string('usernameoremail', 'tool_forgotten');
            $errors['email']    = get_string('usernameoremail', 'tool_forgotten');
            $errors['cpf']      = get_string('usernameoremail', 'tool_forgotten');
        }

        return $errors;
    }

}
