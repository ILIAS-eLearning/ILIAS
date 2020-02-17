<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSimpleSAMLphplIdpDiscovery
 */
class ilSimpleSAMLphplIdpDiscovery extends SimpleSAML\XHTML\IdPDisco implements ilSamlIdpDiscovery
{
    const METADATA_DIRECTORY = 'auth/saml/metadata';

    /**
     * ilSimpleSAMLphplIdpDiscovery constructor.
     */
    public function __construct()
    {
        $this->config = SimpleSAML\Configuration::getInstance();
        $this->metadata = SimpleSAML\Metadata\MetaDataStorageHandler::getMetadataHandler();
        $this->instance = 'saml';
        $this->metadataSets = ['saml20-idp-remote'];
        $this->isPassive = false;
    }

    /**
     * @return string
     */
    public function getMetadataDirectory() : string
    {
        return self::METADATA_DIRECTORY;
    }

    /**
     * @inheritdoc
     */
    public function getList() : array
    {
        return $this->getIdPList();
    }

    /**
     * @param int $idpId
     * @return string
     */
    private function getMetadataPath(int $idpId) : string
    {
        return $this->getMetadataDirectory() . '/' . $idpId . '.xml';
    }

    /**
     * @inheritdoc
     */
    public function storeIdpMetadata(int $idpId, string $metadata) : void
    {
        global $DIC;

        $fs = $DIC->filesystem()->storage();

        $fs->put($this->getMetadataPath($idpId), $metadata);
    }

    /**
     * @inheritdoc
     */
    public function fetchIdpMetadata(int $idpId) : string
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
    public function deleteIdpMetadata(int $idpId) : void
    {
        global $DIC;

        $fs = $DIC->filesystem()->storage();

        if ($fs->has($this->getMetadataPath($idpId))) {
            $fs->delete($this->getMetadataPath($idpId));
        }
    }
}
