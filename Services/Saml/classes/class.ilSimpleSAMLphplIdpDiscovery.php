<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilSimpleSAMLphplIdpDiscovery
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSimpleSAMLphplIdpDiscovery extends SimpleSAML\XHTML\IdPDisco implements ilSamlIdpDiscovery
{
    private const METADATA_DIRECTORY = 'auth/saml/metadata';

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
    public function getMetadataDirectory(): string
    {
        return self::METADATA_DIRECTORY;
    }

    /**
     * @inheritdoc
     */
    public function getList(): array
    {
        return $this->getIdPList();
    }

    /**
     * @param int $idpId
     * @return string
     */
    private function getMetadataPath(int $idpId): string
    {
        return $this->getMetadataDirectory() . '/' . $idpId . '.xml';
    }

    /**
     * @inheritdoc
     */
    public function storeIdpMetadata(int $idpId, string $metadata): void
    {
        global $DIC;

        $fs = $DIC->filesystem()->storage();

        $fs->put($this->getMetadataPath($idpId), $metadata);
    }

    /**
     * @inheritdoc
     */
    public function fetchIdpMetadata(int $idpId): string
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
    public function deleteIdpMetadata(int $idpId): void
    {
        global $DIC;

        $fs = $DIC->filesystem()->storage();

        if ($fs->has($this->getMetadataPath($idpId))) {
            $fs->delete($this->getMetadataPath($idpId));
        }
    }
}
