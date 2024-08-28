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

namespace ILIAS\Export\ExportHandler\Consumer\File\Identifier;

use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\HandlerInterface as ilExportHandlerConsumerFileIdentifierInterface;

class Handler implements ilExportHandlerConsumerFileIdentifierInterface
{
    protected string $identifier;

    public function withIdentifier(
        string $identifier
    ): ilExportHandlerConsumerFileIdentifierInterface {
        $clone = clone $this;
        $clone->identifier = $identifier;
        return $clone;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
