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

use ILIAS\Test\Utilities\TitleColumnsBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Listing\Descriptive as DescriptiveListing;
use ILIAS\UI\Component\Legacy\Content;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;

interface TestUserInteraction
{
    public const LANG_VAR_PREFIX = 'logs_';

    public function getUniqueIdentifier(): ?string;
    public function withId(int $id): self;
    public function getLogEntryAsDataTableRow(
        \ilLanguage $lng,
        TitleColumnsBuilder $title_builder,
        DataRowBuilder $row_builder,
        array $environment
    ): DataRow;
    public function getLogEntryAsExportRow(
        \ilLanguage $lng,
        TitleColumnsBuilder $title_builder,
        AdditionalInformationGenerator $additional_info,
        array $environment
    ): array;
    public function getParsedAdditionalInformation(
        AdditionalInformationGenerator $additional_info,
        UIFactory $ui_factory,
        array $environment
    ): DescriptiveListing|Content;
    public function toStorage(): array;
}
