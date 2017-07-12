<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 */

require_once 'libs/composer/vendor/autoload.php';
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
ilSamlAttributesHolder::setAttributes(array(
	'http://schemas.microsoft.com/ws/2008/06/identity/claims/globalsid'          => array('mjansen'),
	'http://schemas.microsoft.com/ws/2008/06/identity/claims/windowsaccountname' => array('mjansen'),
	'login'     => array('mjansen'),
	'email'     => array('mjansen@databay.de'),
	'gender'    => array('m'),
	'firstname' => array('Michael'),
	'lastname'  => array('Jansen'),
));
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
}

$state = $session->getAuthState();
$stateIdp   = $state['saml:sp:IdP'];
$metadata   = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
$i          = 1;
$idpIndex   = 1;
foreach($metadata->getList('saml20-idp-remote') as $idp)
{
	if($idp['entityid'] == $stateIdp)
	{
		$idpIndex = $i;
		break;
	}

	++$i;
}

$_POST['auth_mode'] = '12_' . $idpIndex;

require_once './Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_SAML);

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

/* @var $ilCtrl ilCtrl */

$ilCtrl->initBaseClass('ilStartUpGUI');
$ilCtrl->setCmd('doSamlAuthentication');
$ilCtrl->setTargetScript('ilias.php');
$ilCtrl->callBaseClass();