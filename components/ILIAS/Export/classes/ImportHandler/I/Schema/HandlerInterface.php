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

namespace ILIAS\Export\ImportHandler\I\Schema;

use ILIAS\Data\Version;
use ILIAS\Export\ImportHandler\I\File\XSD\HandlerInterface as XSDFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\HandlerInterface as XMLFileNodeInfoInterface;
use ILIAS\Export\ImportHandler\I\Schema\HandlerInterface as SchemaInterface;

interface HandlerInterface
{
    public function getXSDFileHandlerByVersionOrLatest(): null|XSDFileHandlerInterface;

    public function getXSDFileHandlerLatest(): null|XSDFileHandlerInterface;

    public function doesXSDFileWithMatchingVersionExist(): bool;

    public function withInformationOf(
        XMLFileNodeInfoInterface $xml_file_node_info
    ): SchemaInterface;

    public function withType(
        string $type
    ): SchemaInterface;

    public function withSubType(
        string $subtype
    ): SchemaInterface;

    public function withVersion(
        Version $version
    ): SchemaInterface;

    public function getMajorVersionString(): string;

    public function getVersion(): null|Version;

    public function getPrimaryType(): null|string;

    public function getSecondaryType(): null|string;

    public function getTypeString(): string;
}
