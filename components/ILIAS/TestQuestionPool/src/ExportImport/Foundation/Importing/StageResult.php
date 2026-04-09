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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Importing;

use ILIAS\UI\Component\Component;

/**
 * Represents the outcome of processing a single import stage. The GUI controller inspects the type to decide whether
 * to render UI components (INTERACT), redirect for the next stage (ADVANCE), display an error (ERROR), or finalize the
 * import (COMPLETE).
 */
class StageResult
{
    /**
     * @param list<Component> $components
     */
    private function __construct(
        public readonly StageResultType $type,
        public readonly ImportContext $context,
        public readonly array $components = [],
        public readonly ?string $error_message = null,
    ) {
    }

    /**
     * Create a result that indicates the process should interrupt to display the given UI components.
     *
     *  @param list<Component> $components
     */
    public static function interact(ImportContext $context, array $components): self
    {
        return new self(StageResultType::INTERACT, $context, $components);
    }

    /**
     * Create a result that indicates the process should advance to the next stage.
     */
    public static function advance(ImportContext $context): self
    {
        return new self(StageResultType::ADVANCE, $context);
    }

    /**
     * Create a result that indicates the process should fail with the given error message.
     */
    public static function error(ImportContext $context, string $message): self
    {
        return new self(StageResultType::ERROR, $context, [], $message);
    }

    /**
     * Create a result that indicates the process should complete successfully.
     */
    public static function complete(ImportContext $context): self
    {
        return new self(StageResultType::COMPLETE, $context);
    }
}
