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

use ILIAS\ILIASObject\Properties\Properties as ObjectProperties;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Table\Table;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Component as UIComponent;
use ILIAS\UI\Component\Modal\RoundTrip as RoundtripModal;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Modal\InterruptiveItem\InterruptiveItem;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Data\URI;
use ILIAS\Language\Language as SystemLanguage;
use ILIAS\HTTP\Services as HTTPService;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Filesystem\Stream\Streams;

class TranslationsTable implements DataRetrieval
{
    private const QUERY_PARAMETER_NAME_SPACE = ['obj', 'trans'];
    private const TOKEN_STRING_ACTION = 'a';
    private const TOKEN_STRING_ROW_ID = 't';
    private const TOKEN_STRING_ACTON_ADDITIONAL = 'aa';

    public const ACTION_EDIT = 'e';
    public const ACTION_MAKE_DEFAULT = 'md';
    public const ACTION_DELETE = 'd';

    private const ACTION_ADDITIONAL_CONFIRM = 'c';
    private const ACTION_ADDITIONAL_SAVE = 's';

    private URLBuilder $url_builder;
    private URLBuilderToken $token_action;
    private URLBuilderToken $token_action_additional;
    private URLBuilderToken $token_row_id;

    /**
     * @param array<ILIAS\ILIASObject\Properties\Translations\Language> $languages
     */
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly SystemLanguage $lng,
        private readonly Refinery $refinery,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly HTTPService $http,
        private Translations $translations,
        private readonly ObjectProperties $object_properties,
        URI $here_uri
    ) {
        [
            $this->url_builder,
            $this->token_action,
            $this->token_row_id,
            $this->token_action_additional
        ] = (new URLBuilder($here_uri))->acquireParameters(
            self::QUERY_PARAMETER_NAME_SPACE,
            self::TOKEN_STRING_ACTION,
            self::TOKEN_STRING_ROW_ID,
            self::TOKEN_STRING_ACTON_ADDITIONAL
        );
    }

    public function runAction(): void
    {
        $action = $this->http->wrapper()->query()->retrieve(
            $this->token_action->getName(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );

        if ($action === '') {
            return;
        }

        match ($action) {
            self::ACTION_EDIT => $this->editTranslation(),
            self::ACTION_MAKE_DEFAULT => $this->makeDefault(),
            self::ACTION_DELETE => $this->deleteTranslations()
        };
    }

    public function getTable(): Table
    {
        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('available_languages'),
            $this->getColumns()
        )->withActions($this->getActions())
            ->withRequest($this->http->request());
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
                $this->url_builder->withParameter(
                    $this->token_action,
                    self::ACTION_EDIT
                ),
                $this->token_row_id
            )->withAsync(),
            self::ACTION_MAKE_DEFAULT => $this->ui_factory->table()->action()->single(
                $this->lng->txt('make_default_language'),
                $this->url_builder->withParameter(
                    $this->token_action,
                    self::ACTION_MAKE_DEFAULT
                ),
                $this->token_row_id
            ),
            self::ACTION_DELETE => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('delete'),
                $this->url_builder->withParameter(
                    $this->token_action,
                    self::ACTION_DELETE
                ),
                $this->token_row_id
            )->withAsync()
        ];
    }

    private function editTranslation(): void
    {
        if ($this->http->wrapper()->query()->retrieve(
            $this->token_action_additional->getName(),
            $this->refinery->kindlyTo()->string()
        ) !== self::ACTION_ADDITIONAL_SAVE) {
            $this->sendAsync(
                $this->buildEditLanguageModal()
            );
        }

        $modal = $this->getAddLanguageModal()
            ->withRequest($this->http->request());
        $data = $modal->getData();
        if ($data === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_title'));
            return;
        }

        $this->translations = $this->translations->withLanguage($data[0]);
        $this->object->getObjectProperties()->storePropertyTranslations(
            $this->translations
        );

        $this->object_properties->storePropertyTranslations(
            $this->translations->withLanguage($data[0])
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
    }

    private function makeDefault(): void
    {
        $this->translations = $this->translations->withDefaultLanguage(
            $this->retrieveAffectedItemsFromQuery()[0]
        );
        $this->object_properties->storePropertyTranslations(
            $this->translations
        );
    }

    private function deleteTranslations(): void
    {
        if ($this->http->wrapper()->query()->retrieve(
            $this->token_action_additional->getName(),
            $this->refinery->kindlyTo()->string()
        ) !== self::ACTION_ADDITIONAL_CONFIRM) {
            $this->sendAsync(
                $this->buildConfirmationModal(
                    $this->retrieveAffectedItemsFromQueryForDeletion()
                )
            );
        }

        $this->object_properties->storePropertyTranslations(
            array_reduce(
                $this->http->wrapper()->post()->retrieve(
                    'interruptive_items',
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->kindlyTo()->string()
                    )
                ),
                static fn(Translations $c, string $v): Translations => $c->withoutLanguage($v),
                $this->translations
            )
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
    }

    private function buildEditLanguageModal(): RoundtripModal
    {
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('edit_language'),
            null,
            $this->translations->getLaguageForCode(
                $this->retrieveAffectedItemsFromQuery()[0]
            )->toForm(
                $this->lng,
                $this->ui_factory->input()->field(),
                $this->refinery
            ),
            $this->url_builder
                ->withParameter($this->token_action, self::ACTION_DELETE)
                ->withParameter($this->token_action_additional, self::ACTION_ADDITIONAL_SAVE)
                ->buildURI()->__toString()
        );
    }

    private function buildConfirmationModal(array $languages_to_delete): InterruptiveModal
    {
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt('obj_conf_delete_lang'),
            $this->url_builder
                ->withParameter($this->token_action, self::ACTION_DELETE)
                ->withParameter($this->token_action_additional, self::ACTION_ADDITIONAL_CONFIRM)
                ->buildURI()->__toString()
        )->withAffectedItems(
            array_map(
                fn(string $v): InterruptiveItem => $this->ui_factory->modal()
                    ->interruptiveItem()->standard($v, $this->lng->txt("meta_l_{$v}")),
                $languages_to_delete
            )
        );
    }

    private function retrieveAffectedItemsFromQueryForDeletion(): array
    {
        $affected_items = $this->retrieveAffectedItemsFromQuery();
        if (in_array($this->translations->getDefaultLanguage(), $affected_items)
            || in_array($this->translations->getMasterLanguage(), $affected_items)) {
            $this->sendAsync(
                $this->ui_factory->messageBox()->failure(
                    $this->lng->txt('default_master_lang_not_deletable')
                )
            );
        }
        return $affected_items;
    }

    private function retrieveAffectedItemsFromQuery(): array
    {
        $affected_items = [];
        if ($this->http->wrapper()->query()->has($this->token_row_id->getName())) {
            $affected_items = $this->http->wrapper()->query()->retrieve(
                $this->token_row_id->getName(),
                $this->refinery->byTrying(
                    [
                        $this->refinery->container()->mapValues(
                            $this->refinery->kindlyTo()->string()
                        ),
                        $this->refinery->always([])
                    ]
                )
            );
        }
        if ($affected_items === []) {
            $this->sendAsync(
                $this->ui_factory->messageBox()->failure(
                    $this->lng->txt('no_checkbox')
                )
            );
        }

        return $affected_items;
    }

    private function sendAsync(UIComponent $response): void
    {
        $this->http->saveResponse(
            $this->http->response()->withBody(
                Streams::ofString(
                    $this->ui_renderer->render($response)
                )
            )
        );
        $this->http->sendResponse();
        $this->http->close();
    }
}
