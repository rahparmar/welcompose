<?php

/**
 * Project: Oak
 * File: validate.js.php
 *
 * Copyright (c) 2006 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id$
 *
 * @copyright 2006 creatics media.systems
 * @author Olaf Gleba
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

// get loader
$path_parts = array(
	dirname(__FILE__),
	'..',
	'core',
	'loader.php'
);
$loader_path = implode(DIRECTORY_SEPARATOR, $path_parts);
require($loader_path);

// start base
/* @var $BASE base */
$BASE = load('base:base');

// deregister globals
$deregister_globals_path = dirname(__FILE__).'/../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_admin_conf = dirname(__FILE__).'/../core/conf/smarty_admin.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_admin_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);


	if (Base_Cnc::filterRequest($_POST['elemID'], OAK_REGEX_ALPHANUMERIC)) {
		
		$reg = '';
		$desc = '';
		
		switch ($_POST['elemID']) {
			case 'name':
				$reg = OAK_REGEX_ALPHANUMERIC;
				$desc = gettext('Nur Zahlen und Buchstaben erlaubt');
			break;
			case 'number':
				$reg = OAK_REGEX_NUMERIC;
				$desc = gettext('Nur Zahlen');
			break;
			case 'url':
				$reg = OAK_REGEX_URL;
				$desc = gettext('Unvollständiger URL');
			break;
			default :
				$reg;
		}	
	}
	
	
	// preparation for later use
	// every relevant regex defined with gettext strings
/*	
	$reg = SHOP_REGEX_NUMERIC;
	$desc = gettext('Only nummeric input are allowed');
	
	$reg = SHOP_REGEX_ALPHANUMERIC;
	$desc = gettext('Only alphanummeric input are allowed');
	
	$reg = SHOP_REGEX_ARTICLE_NUMBER;
	$desc = gettext('Only alphanummeric input are allowed');
	
	$reg = SHOP_REGEX_MANUFACTURER_ARTICLE_NUMBER;
	$desc = gettext('Only alphanummeric input are allowed');
	
	$reg = SHOP_REGEX_EAN;
	$desc = gettext('Only nummeric input is allowed with maximal ');
	
	*/	

	
	if (!empty($_POST['elemVal'])) {
		if (!empty($reg)) {
			if (Base_Cnc::filterRequest($_POST['elemVal'], $reg)) {
				print '<p class="validate"><img src="../static/img/icons/success.gif" /></p>';
			} else {
				print '<p class="validate"><img src="../static/img/icons/error.gif" /> '.$desc.'</p>';
			}
		} else {
			print '<p class="validate"><img src="../static/img/icons/success.gif" /></p>';
		}
	} else {
		//print save empty -> safari doesn't recognized empty properly
		print '&nbsp;';
	}
	
	
		
	// flush the buffer
	@ob_end_flush();
	exit;

} catch (Exception $e) {
	// clean buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}
?>