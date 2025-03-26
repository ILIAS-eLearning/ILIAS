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

namespace ILIAS\ILIASObject\Properties\Translations;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Table;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Data\URI;
use ILIAS\Language\Language as SystemLanguage;
use Psr\Http\Message\ServerRequestInterface;

class TranslationsTable implements DataRetrieval
{
    private const QUERY_PARAMETER_NAME_SPACE = ['obj', 'trans'];
    private const ACTION_TOKEN_STRING = 'a';
    private const ROW_ID_TOKEN_STRING = 't';

    public const ACTION_EDIT = 'e';
    public const ACTION_MAKE_DEFAULT = 'md';
    public const ACTION_DELETE = 'd';

    private URLBuilder $url_builder;
    private URLBuilderToken $action_parameter_token;
    private URLBuilderToken $row_id_token;

    /**
     * @param array<ILIAS\ILIASObject\Properties\Translations\Language> $languages
     */
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly SystemLanguage $lng,
        private readonly ServerRequestInterface $request,
        private readonly Translations $translations,
        URI $here_uri
    ) {
        list($this->url_builder, $this->action_parameter_token, $this->row_id_token) = (new URLBuilder($here_uri))->acquireParameters(
            self::QUERY_PARAMETER_NAME_SPACE,
            self::ACTION_TOKEN_STRING,
            self::ROW_ID_TOKEN_STRING
        );
    }

    public function getTable(): Table
    {
        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('available_languages'),
            $this->getColumns()
        )->withActions($this->getActions())
            ->withRequest($this->request);
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        foreach ($this->translations->getLanguages() as $langauge) {
            yield $langauge->toRow($row_builder, $this->lng);
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return count($this->translations->getLanguages());
    }


    private function getColumns(): array
    {
        $cf = $this->ui_factory->table()->column();
        $columns = [
            'language' => $cf->text($this->lng->txt('language')),
        ];
        if ($this->translations->getContentTranslationActivated()) {
            $columns['master'] = $cf->boolean(
                $this->lng->txt('obj_master_lang'),
                $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_checked.svg', '', 'small'),
                $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_unchecked.svg', '', 'small')
            );
        }

        return $columns + [
            'default' => $cf->boolean(
                $this->lng->txt('default'),
                $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_checked.svg', '', 'small'),
                $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_unchecked.svg', '', 'small')
            ),
            'title' => $cf->text($this->lng->txt('title')),
            'description' => $cf->text($this->lng->txt('description')),
        ];

    }

    private function getActions(): array
    {
        if ($this->translations->migrationMissing()) {
            return [];
        }
        return [
            self::ACTION_EDIT => $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit'),
                $this->url_builder,
                $this->row_id_token
            ),
            self::ACTION_MAKE_DEFAULT => $this->ui_factory->table()->action()->single(
                $this->lng->txt('make_default_language'),
                $this->url_builder,
                $this->row_id_token
            ),
            self::ACTION_DELETE => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('delete'),
                $this->url_builder,
                $this->row_id_token
            )
        ];
    }
}
