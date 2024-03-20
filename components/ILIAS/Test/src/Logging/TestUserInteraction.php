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

namespace ILIAS\Test\Logging;

use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;

/**
 *
 * @author skergomard
 */
interface TestUserInteraction
{
    public function getTestRefId(): int;
    public function getUniqueIdentifier(): ?string;
    public function withId(int $id): self;
    public function getModificationTimestamp(): int;
    public function getLogEntryAsDataTableRow(
        \ilLanguage $lng,
        StaticURLServices $static_url,
        \ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository $properties_repository,
        UIFactory $ui_factory,
        DataRowBuilder $row_builder,
        array $environment
    ): DataRow;
    public function getLogEntryAsCsvRow(): string;
    public function toStorage(): array;
}
