<?php

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

declare(strict_types=1);

namespace ILIAS\Export\ImportHandler\Schema;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Version;
use ILIAS\Export\ImportHandler\I\File\XSD\FactoryInterface as XSDFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\HandlerInterface as XSDFileInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\HandlerInterface as XMLFileNodeInfoInterface;
use ILIAS\Export\ImportHandler\I\SchemaFolder\HandlerInterface as SchemaFolderInterface;
use ILIAS\Export\ImportHandler\I\Schema\HandlerInterface as SchemaInterface;

class Handler implements SchemaInterface
{
    protected SchemaFolderInterface $schema_folder;
    protected ParserFactoryInterface $parser_factory;
    protected XSDFileFactoryInterface $xsd_file_factory;
    protected DataFactory $data_factory;
    protected null|string $type;
    protected null|string $subtype;
    protected null|Version $version;

    public function __construct(
        SchemaFolderInterface $schema_folder,
        DataFactory $data_factory,
        ParserFactoryInterface $parser_factory,
        XSDFileFactoryInterface $xsd_file_factory
    ) {
        $this->schema_folder = $schema_folder;
        $this->parser_factory = $parser_factory;
        $this->xsd_file_factory = $xsd_file_factory;
        $this->data_factory = $data_factory;
        $this->type = null;
        $this->subtype = null;
        $this->version = null;
    }

    public function getXSDFileHandlerByVersionOrLatest(): null|XSDFileInterface
    {
        if (
            is_null($this->getVersion()) ||
            is_null($this->getPrimaryType()) ||
            is_null($this->getSecondaryType())
        ) {
            return null;
        }
        $schema_info = $this->schema_folder->getByVersionOrLatest(
            $this->getVersion(),
            $this->getPrimaryType(),
            $this->getSecondaryType()
        );
        return is_null($schema_info)
            ? null
            : $this->xsd_file_factory->handler()->withFileInfo($schema_info->getFile());
    }

    public function getXSDFileHandlerLatest(): null|XSDFileInterface
    {
        if (
            is_null($this->getPrimaryType()) ||
            is_null($this->getSecondaryType())
        ) {
            return null;
        }
        $schema_info = $this->schema_folder->getLatest(
            $this->getPrimaryType(),
            $this->getSecondaryType()
        );
        return is_null($schema_info)
            ? null
            : $this->xsd_file_factory->handler()->withFileInfo($schema_info->getFile());
    }

    public function doesXSDFileWithMatchingVersionExist(): bool
    {
        if (
            is_null($this->getVersion()) ||
            is_null($this->getPrimaryType()) ||
            is_null($this->getSecondaryType())
        ) {
            return false;
        }
        $schema_info = $this->schema_folder->getByVersion(
            $this->getVersion(),
            $this->getPrimaryType(),
            $this->getSecondaryType()
        );
        return !is_null($schema_info);
    }

    public function withInformationOf(
        XMLFileNodeInfoInterface $xml_file_node_info
    ): SchemaInterface {
        $type_str = $xml_file_node_info->getValueOfAttribute('Entity');
        $types = str_contains($type_str, '_')
            ? explode('_', $type_str)
            : [$type_str, ''];
        $version_str = $xml_file_node_info->hasAttribute('SchemaVersion')
            ? $xml_file_node_info->getValueOfAttribute('SchemaVersion')
            : '';
        if ($version_str === '') {
            return $this
                ->withType($types[0])
                ->withSubType($types[1])
                ->withVersion($this->data_factory->version(((int) ILIAS_VERSION_NUMERIC) . ".0.0"));
        }
        return $this
            ->withType($types[0])
            ->withSubType($types[1])
            ->withVersion($this->data_factory->version($version_str));
    }

    public function withType(
        string $type
    ): SchemaInterface {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function withSubType(
        string $subtype
    ): SchemaInterface {
        $clone = clone $this;
        $clone->subtype = $subtype;
        return $clone;
    }

    public function withVersion(
        Version $version
    ): SchemaInterface {
        $clone = clone $this;
        $clone->version = $version;
        return $clone;
    }

    public function getMajorVersionString(): string
    {
        if (is_null($this->getVersion()) || is_null($this->getVersion()->getMajor())) {
            return ((int) ILIAS_VERSION_NUMERIC) . ".0.0";
        }
        return $this->getVersion()->getMajor() . ".0.0";
    }

    public function getVersion(): null|Version
    {
        return $this->version;
    }

    public function getPrimaryType(): null|string
    {
        return $this->type;
    }

    public function getSecondaryType(): null|string
    {
        return $this->subtype;
    }

    public function getTypeString(): string
    {
        if (is_null($this->getPrimaryType())) {
            return '';
        }
        if (is_null($this->getSecondaryType())) {
            return $this->getPrimaryType();
        }
        return $this->getPrimaryType() . '_' . $this->getSecondaryType();
    }
}
