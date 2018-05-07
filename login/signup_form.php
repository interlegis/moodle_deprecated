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
 * User sign-up form.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');

class login_signup_form extends moodleform implements renderable, templatable {
    function definition() {
        global $USER, $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'createuserandpass', get_string('createuserandpass'), '');

        $mform->addElement('text', 'username', 'CPF', 'maxlength="11" size="11"');
        $mform->setType('username', PARAM_RAW);
        $mform->addRule('username', 'CPF não informado!', 'required', null, 'client');
	$mform->addRule('username', 'Informe apenas dígitos!', 'numeric', null, 'client');
        
        if (!empty($CFG->passwordpolicy)){
            $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
        }
        $mform->addElement('passwordunmask', 'password', get_string('password'), 'maxlength="32" size="12"');
        $mform->setType('password', core_user::get_property_type('password'));
        $mform->addRule('password', get_string('missingpassword'), 'required', null, 'client');

        $mform->addElement('header', 'supplyinfo', get_string('supplyinfo'),'');

        $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
        $mform->setType('email', core_user::get_property_type('email'));
        $mform->addRule('email', get_string('missingemail'), 'required', null, 'client');
        $mform->setForceLtr('email');

        $mform->addElement('text', 'email2', get_string('emailagain'), 'maxlength="100" size="25"');
        $mform->setType('email2', core_user::get_property_type('email'));
        $mform->addRule('email2', get_string('missingemail'), 'required', null, 'client');
        $mform->setForceLtr('email2');

        $namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) {
            $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
            $mform->setType($field, core_user::get_property_type('firstname'));
            $stringid = 'missing' . $field;
            if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
            }
            $mform->addRule($field, get_string($stringid), 'required', null, 'client');
        }

        $mform->addElement('text', 'city', get_string('city'), 'maxlength="120" size="20"');
        $mform->setType('city', core_user::get_property_type('city'));
        if (!empty($CFG->defaultcity)) {
            $mform->setDefault('city', $CFG->defaultcity);
        }

        $country = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country = array_merge($default_country, $country);
        $mform->addElement('select', 'country', get_string('country'), $country);

        if( !empty($CFG->country) ){
            $mform->setDefault('country', $CFG->country);
        }else{
            $mform->setDefault('country', '');
        }

        profile_signup_fields($mform);

        if (signup_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('security_question', 'auth'), array('https' => $CFG->loginhttps));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            $mform->closeHeaderBefore('recaptcha_element');
        }

        if (!empty($CFG->sitepolicy)) {
            $mform->addElement('header', 'policyagreement', get_string('policyagreement'), '');
            $mform->setExpanded('policyagreement');
            $mform->addElement('static', 'policylink', '', '<a href="'.$CFG->sitepolicy.'" onclick="this.target=\'_blank\'">'.get_String('policyagreementclick').'</a>');
            $mform->addElement('checkbox', 'policyagreed', get_string('policyaccept'));
            $mform->addRule('policyagreed', get_string('policyagree'), 'required', null, 'client');
        }

        // buttons
        $this->add_action_buttons(true, get_string('createaccount'));

    }

    function definition_after_data(){
        $mform = $this->_form;
        $mform->applyFilter('username', 'trim');

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }
    }


    function validation($data, $files) {

	    function validatecpf($cpf) {
 	       // Verifica se um numero foi informado.
		if (is_null($cpf)) {
		    return false;
		}
		if (!is_numeric($cpf)) {
		    return false;
		}
		//$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

		// Verifica se o numero de digitos informados eh igual a 11.
		if (strlen($cpf) != 11) {
		    return false;
		} else if ($cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' ||
		    $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' ||
		    $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' ||
		    $cpf == '99999999999') {
		    return false;
		} else {
		    // Calcula os digitos verificadores para verificar se o CPF eh valido.

			$cpf = preg_replace('/[^0-9]/', '', (string) $cpf);
			// Valida tamanho
			if (strlen($cpf) != 11)
				return false;
			// Calcula e confere primeiro dígito verificador
			for ($i = 0, $j = 10, $soma = 0; $i < 9; $i++, $j--)
				$soma += $cpf{$i} * $j;
			$resto = $soma % 11;
			if ($cpf{9} != ($resto < 2 ? 0 : 11 - $resto))
				return false;
			// Calcula e confere segundo dígito verificador
			for ($i = 0, $j = 11, $soma = 0; $i < 10; $i++, $j--)
				$soma += $cpf{$i} * $j;
			$resto = $soma % 11;
			
			$resultado = $cpf{10} == ($resto < 2 ? 0 : 11 - $resto);
			return $resultado;
		}
	    }


        $errors = parent::validation($data, $files);

        if (signup_captcha_enabled()) {
            $recaptcha_element = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['recaptcha_challenge_field'])) {
                $challenge_field = $this->_form->_submitValues['recaptcha_challenge_field'];
                $response_field = $this->_form->_submitValues['recaptcha_response_field'];
                if (true !== ($result = $recaptcha_element->verify($challenge_field, $response_field))) {
                    $errors['recaptcha'] = $result;
                }
            } else {
                $errors['recaptcha'] = get_string('missingrecaptchachallengefield');
            }
        }

	// Valida CPF
	$cpf = $this->_form->getElement('username')->getValue();
	$cpfValido = validatecpf($cpf);
	if(!$cpfValido) {
             $errors['username'] = 'CPF inválido!';
	}

        $errors += signup_validate_data($data, $files);

        return $errors;

    }

    
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();
        $context = [
            'formhtml' => $formhtml
        ];
        return $context;
    }
}
