<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Saml/interfaces/interface.ilSamlIdpDiscovery.php';

/**
 * Class ilSimpleSamlIdpDiscovery
 */
class ilSimpleSamlIdpDiscovery extends SimpleSAML_XHTML_IdPDisco implements ilSamlIdpDiscovery
{
	const METADATA_PATH = 'auth/saml/metadata';

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
	 * @return string
	 */
	public function getMetadataPath()
	{
		return self::METADATA_PATH;
	}

	/**
	 * @inheritdoc
	 */
	public function getList()
	{
		return $this->getIdPList();
	}

	/**
	 * @inheritdoc
	 */
	public function storeIdpMetadata($idpId, $metadata)
	{
		global $DIC;

		$fs = $DIC->filesystem()->storage();

		$fs->put($this->getMetadataPath() . '/' . $idpId . '.xml', $metadata);
	}

	/**
	 * @inheritdoc
	 */
	public function fetchIdpMetadata($idpId)
	{
		global $DIC;

		$fs = $DIC->filesystem()->storage();

		return $fs->read($this->getMetadataPath() . '/' . $idpId . '.xml');
	}
}