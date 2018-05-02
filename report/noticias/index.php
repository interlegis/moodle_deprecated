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
 * Config changes report
 *
 * @package    report
 * @subpackage configlog
 * @copyright  2009 Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('index_form.php');
require_once('index_form2.php');


// page parameters
$page    = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 30, PARAM_INT);    // how many per page
$sort    = optional_param('sort', 'timemodified', PARAM_ALPHA);
$dir     = optional_param('dir', 'DESC', PARAM_ALPHA);

admin_externalpage_setup('reportnoticias', '', null, '', array('pagelayout'=>'report'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('noticias', 'report_noticias'));

$mform = new index_form();
$mform2 = new index_form2();


$mform->display();
$mform2->display();

$sql = "SELECT * FROM {noticias};";

$ti = $DB->get_fieldset_select('noticias', 'titulo');
$au = $DB->get_fieldset_select('noticias', 'autor');
$co = $DB->get_fieldset_select('noticias', 'conteudo');

for($i = 0; $i < 2; $i++)
{
	echo 'Autor: '.$au[$i].'.<br>';
	echo 'Título: '.$ti[$i].'.<br>';
	echo 'Conteúdo: '.$co[$i].'.<br><br><br>';
}

echo $OUTPUT->footer();
