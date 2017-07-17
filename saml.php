<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 */

require_once 'libs/composer/vendor/autoload.php';
$as = new SimpleSAML_Auth_Simple('default-sp');

if(isset($_GET['action']) && $_GET['action'] == 'logout')
{
	$params = array(
		'ReturnStateParam' => 'LogoutState',
		'ReturnStateStage' => 'ilLogoutState'
	);

	if(isset($_GET['logout_url']) && strlen($_GET['logout_url']))
	{
		$params['ReturnTo']= $_GET['logout_url'];
	}

	$as->logout($params);
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

require_once 'Services/Saml/classes/class.ilAuthFrontendCredentialsSaml.php';
ilAuthFrontendCredentialsSaml::setRequestAttributes($as->getAttributes());

if(strlen($session->getData('example:set_target', 'il_target')))
{
	$_GET['target'] = $session->getData('example:set_target', 'il_target');
	$session->deleteData('example:set_target', 'il_target');
}

if($store_type == 'phpsession' || empty($store_type))
{
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

/*
 	The complete code above could be moved to ilStartupGUI::doSamlAuthentication in case we use the SQLite session store.
	If "phpsession" is used as session storage, we have to close the SimpleSAML session (see code above) before we trigger
	the ILIAS initialisation.
	If so, we could pass (via setter or constructor injection) the SAML attribute array to the ilAuthFrontendCredentialsSaml instance
	instead of using a static method. Furthermore we could read the config file from a defined location or/and get the SP name from
	database instead of using the "default-sp" identifier.
*/

require_once './Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_SAML);

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

/* @var $ilCtrl ilCtrl */

$ilCtrl->initBaseClass('ilStartUpGUI');
$ilCtrl->setCmd('doSamlAuthentication');
$ilCtrl->setTargetScript('ilias.php');
$ilCtrl->callBaseClass();