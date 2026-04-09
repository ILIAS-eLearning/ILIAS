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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\StageResult;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A single step in a multi-stage import workflow. Each stage is called by the ImportStageRunner and returns a
 * StageResult that determines the next action (render UI, advance, report error, or complete).
 */
interface ImportStage
{
    /**
     * Get the unique identifier of the stage.
     */
    public function getIdentifier(): string;

    /**
     * Get the label of the stage which will be displayed in the workflow UI.
     */
    public function getLabel(): string;

    /**
     * Get the description of the stage which will be displayed in the workflow UI.
     */
    public function getDescription(): string;

    /**
     * Process the current stage. On the first invocation the stage should return `StageResult::interact()` with the UI
     * components to display. On form submission it should validate the input and return `StageResult::advance()` or
     * `StageResult::error()`.
     */
    public function process(ImportContext $context, ServerRequestInterface $request): StageResult;
}
