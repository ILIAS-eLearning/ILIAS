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

namespace ILIAS\Export\ExportHandler\PublicAccess\Repository\Element;

use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\HandlerInterface as ilExportHandlerPublicAccessRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\HandlerInterface as ilExportHandlerPublicAccessRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Values\HandlerInterface as ilExportHandlerPublicAccessRepositoryValuesInterface;

class Handler implements ilExportHandlerPublicAccessRepositoryElementInterface
{
    protected ilExportHandlerPublicAccessRepositoryKeyInterface $key;
    protected ilExportHandlerPublicAccessRepositoryValuesInterface $values;

    public function withKey(
        ilExportHandlerPublicAccessRepositoryKeyInterface $key
    ): ilExportHandlerPublicAccessRepositoryElementInterface {
        $clone = clone $this;
        $clone->key = $key;
        return $clone;
    }

    public function withValues(
        ilExportHandlerPublicAccessRepositoryValuesInterface $values
    ): ilExportHandlerPublicAccessRepositoryElementInterface {
        $clone = clone $this;
        $clone->values = $values;
        return $clone;
    }

    public function getKey(): ilExportHandlerPublicAccessRepositoryKeyInterface
    {
        return $this->key;
    }

    public function getValues(): ilExportHandlerPublicAccessRepositoryValuesInterface
    {
        return $this->values;
    }

    public function isStorable(): bool
    {
        return (
            ($this->getKey()->isValid() ?? false) and
            ($this->getValues()->isValid() ?? false)
        );
    }
}
