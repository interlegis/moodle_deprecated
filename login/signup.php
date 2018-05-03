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
 * user signup page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->libdir . '/authlib.php');

// Try to prevent searching for sites that allow sign-up.
if (!isset($CFG->additionalhtmlhead)) {
    $CFG->additionalhtmlhead = '';
}
$CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';

if (!$authplugin = signup_is_enabled()) {
    print_error('notlocalisederrormessage', 'error', '', 'Sorry, you may not use this page.');
}

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_url('/login/signup.php');
$PAGE->set_context(context_system::instance());

// If wantsurl is empty or /login/signup.php, override wanted URL.
// We do not want to end up here again if user clicks "Login".
if (empty($SESSION->wantsurl)) {
    $SESSION->wantsurl = $CFG->wwwroot . '/';
} else {
    $wantsurl = new moodle_url($SESSION->wantsurl);
    if ($PAGE->url->compare($wantsurl, URL_MATCH_BASE)) {
        $SESSION->wantsurl = $CFG->wwwroot . '/';
    }
}

if (isloggedin() and !isguestuser()) {
    // Prevent signing up when already logged in.
    echo $OUTPUT->header();
    echo $OUTPUT->box_start();
    $logout = new single_button(new moodle_url($CFG->httpswwwroot . '/login/logout.php',
        array('sesskey' => sesskey(), 'loginpage' => 1)), get_string('logout'), 'post');
    $continue = new single_button(new moodle_url('/'), get_string('cancel'), 'get');
    echo $OUTPUT->confirm(get_string('cannotsignup', 'error', fullname($USER)), $logout, $continue);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit;
}

$mform_signup = $authplugin->signup_form();

if ($mform_signup->is_cancelled()) {
  redirect(get_login_url());

} else if ($user = $mform_signup->get_data()) {
    // Add missing required fields.
    $user = signup_setup_new_user($user);

    $authplugin->user_signup($user, true); // prints notice and link to login/index.php
    exit; //never reached
}

// make sure we really are on the https page when https login required
$PAGE->verify_https_required();


$newaccount = get_string('newaccount');
$login      = get_string('login');

$PAGE->navbar->add($login);
$PAGE->navbar->add($newaccount);

$PAGE->set_pagelayout('login');
$PAGE->set_title($newaccount);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

echo $OUTPUT->render($mform_signup);
echo $OUTPUT->footer();

// require_once($CFG->dirroot.'/user/profile/field/cpf/field.class.php');
// $cpf = $formfield->display_data();

$cpf = "<script>document.getElementById(\"profilefield_cpf\").value</script>";

echo $cpf;

function validatecpf($cpf = null)  {   // Verifica se um número foi informado
    
    if(empty($cpf)) {
        return false;
    }
 
    // Elimina possivel mascara
    $cpf = str_replace('[^0-9]', '', $cpf);
    $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
     
    // Verifica se o numero de digitos informados é igual a 11 
    if (strlen($cpf) != 11) {
        return false;
    }
    // Verifica se nenhuma das sequências invalidas abaixo 
    // foi digitada. Caso afirmativo, retorna falso
    else if ($cpf == '00000000000' || 
        $cpf == '11111111111' || 
        $cpf == '22222222222' || 
        $cpf == '33333333333' || 
        $cpf == '44444444444' || 
        $cpf == '55555555555' || 
        $cpf == '66666666666' || 
        $cpf == '77777777777' || 
        $cpf == '88888888888' || 
        $cpf == '99999999999') {
            echo "<h1>Falso</h1>";
        return false;
     // Calcula os digitos verificadores para verificar se o
     // CPF é válido
     } else {   
         
        for ($t = 9; $t < 11; $t++) {
             
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf{$c} * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf{$c} != $d) {
                return false;
            }
        }
            echo "<h1>TRUE</h1>";
        return true;
    }
}

$valido = validatecpf($cpf);

if ($valido == false) {

    echo "<script>document.getElementById('id_error_username').style.display = 'block'";
    echo "<script>document.getElementById('id_error_username').innerHTML = 'CPF inválido';</script>";
}

?>

<script type="text/javascript">

    var child = document.getElementById("id_error_username");
    child.style.display = "none";
    
    var cpf = document.getElementById("profilefield_cpf");
    cpf.onkeypress = cpf.onpaste = checkInput;

    function checkInput(e) {
        var e = e || event;
        var char = e.type == 'keypress' 
            ? String.fromCharCode(e.keyCode || e.which) 
            : (e.clipboardData || window.clipboardData).getData('Text');
        if (/[^\d]/gi.test(char)) {
            return false;
        }
    }


</script>

<!--  -->
