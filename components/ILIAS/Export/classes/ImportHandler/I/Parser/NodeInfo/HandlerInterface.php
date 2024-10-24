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

namespace ILIAS\Export\ImportHandler\I\Parser\NodeInfo;

use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\CollectionInterface as XMLFileNodeInfoCollectionInterface;

interface HandlerInterface
{
    public function getXML(): string;

    public function getNodeName(): string;

    public function getValueOfAttribute(string $attribute_name): string;

    public function getChildren(): XMLFileNodeInfoCollectionInterface;

    public function getParent(): HandlerInterface|null;

    public function hasAttribute(string $attribute_name): bool;

    public function toString(): string;
}
