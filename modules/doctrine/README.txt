Doctrine models are currently being put in
	application/models/doctrine

TO INSTALL
==========

Copy folders over the top of existing ones.

On Linux/Unix/OSX set the permissions to 777 (or chown the folders as appropriate) so the web server can write to the following folders:

application/config/doctrine/sql
application/config/doctrine/fixtures
application/config/doctrine/migrations

chmod 755
modules/doctrine/doctrine

Grab a copy of Doctrine and put it in the modules/doctrine/vendor/ directory. When installed correctly you will have the following directory layout:

modules/doctrine/vendor/
                        Doctrine.php
                        Doctrine/

Open application/config/config.php and enable the Doctrine module:

$config['modules'] = array
(
  MODPATH.'doctrine',
  // Any others you have go here...
);

Don't forget to check 
$config['enable_hooks'] = TRUE;

or nothing will happen!

Sample controller etc coming at some point - for now I assume you're familiar with Doctrine enough to get started.