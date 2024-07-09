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

namespace ILIAS\Test\Logging;

use ILIAS\Test\Export\CSVExportTrait;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;

class TestError implements TestUserInteraction
{
    use CSVExportTrait;
    use ColumnsHelperFunctionsTrait;

    public const IDENTIFIER = 'te';

    private int $id;

    public function __construct(
        private readonly int $test_ref_id,
        private readonly ?int $question_id,
        private readonly ?int $admin_id,
        private readonly ?int $pax_id,
        private readonly TestErrorTypes $interaction_type,
        private readonly int $modification_timestamp,
        private readonly string $error_message
    ) {

    }

    public function getUniqueIdentifier(): ?string
    {
        return self::IDENTIFIER . '_' . $this->id;
    }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getLogEntryAsDataTableRow(
        \ilLanguage $lng,
        StaticURLServices $static_url,
        GeneralQuestionPropertiesRepository $properties_repository,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        DataRowBuilder $row_builder,
        array $environment
    ): DataRow {
        $test_obj_id = \ilObject::_lookupObjId($this->test_ref_id);
        $admin = $this->getUserForPresentation($this->admin_id);
        $pax = $this->getUserForPresentation($this->pax_id);

        return $row_builder->buildDataRow(
            $this->getUniqueIdentifier(),
            [
                'date_and_time' => new \DateTimeImmutable(
                    "@{$this->modification_timestamp}",
                    $environment['timezone']
                ),
                'corresponding_test' => $this->buildTestTitleColumnContent(
                    $lng,
                    $static_url,
                    $ui_factory->link(),
                    $ui_renderer,
                    $this->test_ref_id
                ),
                'admin' => $admin,
                'participant' => $pax,
                'ip' => '',
                'question' => $this->buildQuestionTitleColumnContent(
                    $properties_repository,
                    $lng,
                    $static_url,
                    $ui_factory->link(),
                    $ui_renderer,
                    $this->question_id,
                    $this->test_ref_id
                ),
                'log_entry_type' => $lng->txt(self::LANG_VAR_PREFIX . self::IDENTIFIER),
                'interaction_type' => $lng->txt(self::LANG_VAR_PREFIX . $this->interaction_type->value)
            ]
        );
    }

    public function getParsedAdditionalInformation(
        AdditionalInformationGenerator $additional_info,
        UIFactory $ui_factory,
        array $environment
    ): Legacy {
        return $ui_factory->legacy($this->error_message);
    }

    public function getLogEntryAsCsvRow(
        \ilLanguage $lng,
        GeneralQuestionPropertiesRepository $properties_repository,
        AdditionalInformationGenerator $additional_info,
        array $environment
    ): string {
        $admin = $this->getUserForPresentation($this->admin_id);
        $pax = $this->getUserForPresentation($this->pax_id);

        return implode(
            ';',
            $this->processCSVRow(
                [
                    (new \DateTimeImmutable(
                        "@{$this->modification_timestamp}",
                        $environment['timezone']
                    ))->format($environment['date_format']),
                    $this->buildTestTitleCSVContent($lng, $this->test_ref_id),
                    $admin,
                    $pax,
                    '',
                    $this->buildQuestionTitleCSVContent(
                        $properties_repository,
                        $lng,
                        $this->question_id
                    ),
                    $lng->txt(self::LANG_VAR_PREFIX . self::IDENTIFIER),
                    $lng->txt(self::LANG_VAR_PREFIX . $this->interaction_type->value),
                    $this->error_message
                ]
            )
        ) . "\n";
    }

    public function toStorage(): array
    {
        return [
            'ref_id' => [\ilDBConstants::T_INTEGER , $this->test_ref_id],
            'qst_id' => [\ilDBConstants::T_INTEGER , $this->question_id],
            'admin_id' => [\ilDBConstants::T_INTEGER , $this->admin_id],
            'pax_id' => [\ilDBConstants::T_INTEGER , $this->pax_id],
            'interaction_type' => [\ilDBConstants::T_TEXT , $this->interaction_type->value],
            'modification_ts' => [\ilDBConstants::T_INTEGER , $this->modification_timestamp],
            'error_message' => [\ilDBConstants::T_TEXT , $this->error_message]
        ];
    }

    private function getUserForPresentation(?int $user_id): string
    {
        if ($user_id === null) {
            return '';
        }
        return \ilUserUtil::getNamePresentation(
            $user_id,
            false,
            false,
            '',
            true
        );
    }
}
