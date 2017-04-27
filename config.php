<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost    = '10.1.2.47';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'saberes';
$CFG->dbpass    = 'senhas@beres';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbsocket' => '',
);

$CFG->wwwroot   = 'http://moodleh.interlegis.leg.br/moodle';
$CFG->dataroot  = '/var/www/moodledata';
//'/var/aplicacoes/saberes/moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!


