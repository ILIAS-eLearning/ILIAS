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

namespace ILIAS\Export\ImportStatus;

use ILIAS\Export\ImportStatus\I\Content\ilHandlerInterface as ilImportStatusContentInterface;
use ILIAS\Export\ImportStatus\I\ilHandlerInterface as ilImportStatusHandlerInterface;

class ilHandler implements ilImportStatusHandlerInterface
{
    private StatusType $type;
    private ilImportStatusContentInterface $content;

    public function __construct()
    {
        $this->type = StatusType::NONE;
    }

    public function getType(): StatusType
    {
        return $this->type;
    }

    public function withType(StatusType $type): ilImportStatusHandlerInterface
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function getContent(): ilImportStatusContentInterface
    {
        return $this->content;
    }

    public function withContent(ilImportStatusContentInterface $content): ilImportStatusHandlerInterface
    {
        $clone = clone $this;
        $clone->content = $content;
        return $clone;
    }
}
