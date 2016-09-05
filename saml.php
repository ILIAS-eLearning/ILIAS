<?php
// saml-patch: begin
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 */

require_once 'Services/Saml/lib/simplesamlphp/lib/_autoload.php';

$as = new SimpleSAML_Auth_Simple('default-sp');

if(isset($_GET['action']) && $_GET['action'] == 'logout' && isset($_GET['logout_url']) && strlen($_GET['logout_url']))
{
	$as->logout(array(
		'ReturnTo'         => $_GET['logout_url'],
		'ReturnStateParam' => 'LogoutState',
		'ReturnStateStage' => 'ilLogoutState'
	));
}

$saml_config = SimpleSAML_Configuration::getInstance();
$store_type  = $saml_config->getString('store.type', false);

$session = SimpleSAML_Session::getSessionFromRequest();

if(isset($_GET['target']) && !isset($_GET['returnTo']))
{
	$_GET['returnTo'] = $_GET['target'];
}

if(isset($_GET['returnTo']))
{
	$session->setData('example:set_target', 'il_target', $_GET['returnTo']);
}
else
{
	$session->deleteData('example:set_target', 'il_target');
}

$as->requireAuth();


require_once 'Services/Saml/classes/class.ilSamlAttributesHolder.php';
ilSamlAttributesHolder::setAttributes($as->getAttributes());

if(strlen($session->getData('example:set_target', 'il_target')))
{
	ilSamlAttributesHolder::setReturnTo($session->getData('example:set_target', 'il_target'));
	$session->deleteData('example:set_target', 'il_target');
}

$GLOBALS['saml_auth_phpsession'] = false;
if($store_type == 'phpsession' || empty($store_type))
{
	$GLOBALS['saml_auth_phpsession'] = true;
session_write_close();

session_name('PHPSESSID');
session_id('');
}

$_POST['auth_mode'] = '99_1';
require_once './Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

/* @var $ilCtrl ilCtrl */

$ilCtrl->initBaseClass('ilStartUpGUI');
$ilCtrl->setCmd('showLogin');
$ilCtrl->setTargetScript('ilias.php');
$ilCtrl->callBaseClass();
// saml-patch: end