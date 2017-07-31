<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'bdsaberes01a.interlegis.leg.br';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'matheusg';
$CFG->dbpass    = 'akmtmd552182';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbsocket' => '',
);

$CFG->wwwroot   = 'http://127.0.0.1/moodle';
$CFG->dataroot  = '/var/www/moodledata';
//'/var/aplicacoes/saberes/moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

//=========================================================================
// 7. SETTINGS FOR DEVELOPMENT SERVERS - not intended for production use!!!
//=========================================================================
//
// Force a debugging mode regardless the settings in the site administration
# @error_reporting(E_ALL | E_STRICT);   // NOT FOR PRODUCTION SERVERS!
# @ini_set('display_errors', '1');         // NOT FOR PRODUCTION SERVERS!
# $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
# $CFG->debugdisplay = 1;              // NOT FOR PRODUCTION SERVERS!
//
// You can specify a comma separated list of user ids that that always see
// debug messages, this overrides the debug flag in $CFG->debug and $CFG->debugdisplay

// for these users only.
# $CFG->debugusers = '2';
