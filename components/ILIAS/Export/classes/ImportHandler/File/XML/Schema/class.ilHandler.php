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

namespace ILIAS\Export\ImportHandler\File\XML\Schema;

use ILIAS\Data\Version;
use ILIAS\Export\ImportHandler\I\File\Path\ilFactoryInterface as ilFilePathFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Schema\ilHandlerInterface as ilXMLFileSchemaHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\ilFactoryInterface as ilXSDFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ILIAS\Export\Schema\ilXmlSchemaFactory;

class ilHandler implements ilXMLFileSchemaHandlerInterface
{
    protected ilXmlSchemaFactory $schema;
    protected ilFilePathFactoryInterface $path;
    protected ilParserFactoryInterface $parser;
    protected ilXSDFileFactoryInterface $xsd;
    protected null|string $type;
    protected null|string $subtype;
    protected null|Version $version;
    protected null|ilXMLFileHandlerInterface $xml_file_handler;
    protected null|ilXMLFileNodeInfoInterface $xml_file_node_info;

    public function __construct(
        ilFilePathFactoryInterface $path,
        ilXmlSchemaFactory $schema,
        ilParserFactoryInterface $parser,
        ilXSDFileFactoryInterface $xsd
    ) {
        $this->path = $path;
        $this->schema = $schema;
        $this->parser = $parser;
        $this->xsd = $xsd;
        $this->type = null;
        $this->subtype = null;
        $this->version = null;
        $this->xml_file_handler = null;
        $this->xml_file_node_info = null;
    }

    public function getXSDFileHandlerByVersionOrLatest(): null|ilXSDFileHandlerInterface
    {
        if (
            is_null($this->getVersion()) ||
            is_null($this->getPrimaryType()) ||
            is_null($this->getSecondaryType())
        ) {
            return null;
        }
        $latest_file_info = $this->schema->getByVersionOrLatest(
            $this->getVersion(),
            $this->getPrimaryType(),
            $this->getSecondaryType()
        );
        return is_null($latest_file_info)
            ? null
            : $this->xsd->withFileInfo($latest_file_info);
    }

    public function getXSDFileHandlerLatest(): null|ilXSDFileHandlerInterface
    {
        if (
            is_null($this->getPrimaryType()) ||
            is_null($this->getSecondaryType())
        ) {
            return null;
        }
        $latest_file_info = $this->schema->getLatest(
            $this->getPrimaryType(),
            $this->getSecondaryType()
        );
        return is_null($latest_file_info)
            ? null
            : $this->xsd->withFileInfo($latest_file_info);
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
        $file_info_with_version = $this->schema->getByVersion(
            $this->getVersion(),
            $this->getPrimaryType(),
            $this->getSecondaryType()
        );
        return !is_null($file_info_with_version);
    }

    public function withType(string $type): ilXMLFileSchemaHandlerInterface
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function withSubType(string $subtype): ilXMLFileSchemaHandlerInterface
    {
        $clone = clone $this;
        $clone->subtype = $subtype;
        return $clone;
    }

    public function withVersion(Version $version): ilXMLFileSchemaHandlerInterface
    {
        $clone = clone $this;
        $clone->version = $version;
        return $clone;
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
