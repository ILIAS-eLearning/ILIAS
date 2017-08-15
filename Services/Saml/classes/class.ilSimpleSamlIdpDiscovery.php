<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Saml/interfaces/interface.ilSamlIdpDiscovery.php';

/**
 * Class ilSimpleSamlIdpDiscovery
 */
class ilSimpleSamlIdpDiscovery extends SimpleSAML_XHTML_IdPDisco implements ilSamlIdpDiscovery
{
	/**
	 * ilSimpleSamlIdpDiscovery constructor.
	 */
	public function __construct()
	{
		$this->config       = SimpleSAML_Configuration::getInstance();
		$this->metadata     = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
		$this->instance     = 'saml';
		$this->metadataSets = array('saml20-idp-remote');
		$this->isPassive    = false;
	}

	/**
	 * @inheritdoc
	 */
	public function getList()
	{
		return $this->getIdPList();
	}
}