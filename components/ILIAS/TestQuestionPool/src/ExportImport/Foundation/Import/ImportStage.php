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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Import\ImportSession;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Import\StageResult;

/**
 * One step of an import wizard. The wizard calls the stage and continues, stops for the UI, or reports an error
 * based on the returned StageResult.
 */
interface ImportStage
{
    /**
     * Get the unique identifier of the stage.
     */
    public function getIdentifier(): string;

    /**
     * Get the label of the stage which will be displayed in the workflow UI. If null, the stage will not be displayed
     * in the workflow UI.
     */
    public function getLabel(): ?string;

    /**
     * Get the description of the stage which will be displayed in the workflow UI. If null, the stage will not be
     * displayed in the workflow UI.
     */
    public function getDescription(): ?string;

    /**
     * Process the current stage. A stage that needs input returns `StageResult::requiresUserInteraction()`. A finished
     * stage returns `StageResult::completedSuccessfully()`. A failed stage returns `StageResult::error()`.
     */
    public function process(ImportSession $session): StageResult;
}
