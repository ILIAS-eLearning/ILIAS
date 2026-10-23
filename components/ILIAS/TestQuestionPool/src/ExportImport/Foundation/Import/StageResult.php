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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Import;

use ILIAS\UI\Component\Component;

/**
 * Outcome of one import stage. The wizard decides whether another stage follows.
 */
class StageResult
{
    /**
     * @param list<Component> $components
     */
    private function __construct(
        public readonly StageResultType $type,
        public readonly ImportSession $session,
        public readonly array $components = [],
        public readonly ?string $error_message = null,
    ) {
    }

    /**
     * @param list<Component> $components
     */
    public static function requiresUserInteraction(ImportSession $session, array $components): self
    {
        return new self(StageResultType::RequiresUserInteraction, $session, $components);
    }

    public static function error(ImportSession $session, string $message): self
    {
        return new self(StageResultType::Error, $session, [], $message);
    }

    public static function completedSuccessfully(ImportSession $session): self
    {
        return new self(StageResultType::CompletedSuccessfully, $session);
    }
}
