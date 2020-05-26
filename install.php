<?php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

$modSettings['disableQueryCheck'] = 1;
global $smcFunc;

$smcFunc['db_query']('', "ALTER TABLE {db_prefix}members ADD COLUMN viber_id varchar(24) NOT NULL;");
$smcFunc['db_query']('', "ALTER TABLE {db_prefix}members ADD COLUMN viber_on tinyint(1) DEFAULT 0;");

?>
