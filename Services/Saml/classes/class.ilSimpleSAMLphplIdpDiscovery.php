<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Saml/interfaces/interface.ilSamlIdpDiscovery.php';

/**
 * Class ilSimpleSAMLphplIdpDiscovery
 */
class ilSimpleSAMLphplIdpDiscovery extends SimpleSAML_XHTML_IdPDisco implements ilSamlIdpDiscovery
{
    const METADATA_DIRECTORY = 'auth/saml/metadata';

    /**
     * ilSimpleSAMLphplIdpDiscovery constructor.
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
    public function getMetadataDirectory()
    {
        return self::METADATA_DIRECTORY;
    }

    /**
     * @inheritdoc
     */
    public function getList()
    {
        return $this->getIdPList();
    }

    /**
     * @param int $idpId
     * @return string
     */
    protected function getMetadataPath($idpId)
    {
        return $this->getMetadataDirectory() . '/' . $idpId . '.xml';
    }

    /**
     * @inheritdoc
     */
    public function storeIdpMetadata($idpId, $metadata)
    {
        global $DIC;

        $fs = $DIC->filesystem()->storage();

        $fs->put($this->getMetadataPath($idpId), $metadata);
    }

    /**
     * @inheritdoc
     */
    public function fetchIdpMetadata($idpId)
    {
        global $DIC;

        $fs = $DIC->filesystem()->storage();

        if (!$fs->has($this->getMetadataPath($idpId))) {
            return '';
        }

        return $fs->read($this->getMetadataPath($idpId));
    }

    /**
     * @inheritdoc
     */
    public function deleteIdpMetadata($idpId)
    {
        global $DIC;

        $fs = $DIC->filesystem()->storage();

        if ($fs->has($this->getMetadataPath($idpId))) {
            $fs->delete($this->getMetadataPath($idpId));
        }
    }
}
