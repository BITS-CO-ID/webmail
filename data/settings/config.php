<?php

$aSieveDomains = array('imap.domain1.com', 'imap.domain2.com');

return array(
	
	'plugins.ispconfig-change-password.config.host' => '127.0.0.1',
	'plugins.ispconfig-change-password.config.dbuser' => 'root',
	'plugins.ispconfig-change-password.config.dbpassword' => "suck-IT26",
	'plugins.ispconfig-change-password.config.dbname' => 'dbispconfig',
	'plugins.afterlogic-dropbox-plugin' => true,
	'sieve' => false,
	'sieve.autoresponder' => true,
	'sieve.forward' => true,
	'sieve.filters' => true,
	'sieve.config.host' => '',
	'sieve.config.port' => 2000,
	'sieve.config.filters-folder-charset' => 'utf-8', // [utf7-imap, utf-8]
	'sieve.config.domains' => $aSieveDomains,

	'labs.x-frame-options' => 'SAMEORIGIN',

	'links.importing-contacts' => 'http://www.afterlogic.com/wiki/Importing_contacts_(WebMail_Lite)'
	
);
