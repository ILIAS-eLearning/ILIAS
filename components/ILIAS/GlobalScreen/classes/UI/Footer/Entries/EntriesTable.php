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

namespace ILIAS\GlobalScreen\UI\Footer\Entries;

use ILIAS\UI\Factory;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table\Ordering;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\UI\Component\Table\OrderingRetrieval;
use ILIAS\UI\Component\Table\OrderingRowBuilder;
use ILIAS\GlobalScreen\UI\Footer\Groups\Group;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslationsRepository;
use ILIAS\GlobalScreen\GUI\I18n\Translator;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\GlobalScreen\GUI\Input\TokenContainer;

class EntriesTable implements OrderingRetrieval
{
    use Hasher;

    public const COLUMN_ACTIVE = 'active';
    public const COLUMN_TITLE = 'title';
    public const CLUMNS_ITEMS = 'items';
    private Factory $ui_factory;
    private ServerRequestInterface $request;
    private URLBuilder $url_builder;
    private URLBuilderToken $id_token;
    private Translator $translator;
    private TranslationsRepository $translations_repository;

    public function __construct(
        private Pons $pons,
        private readonly Group $group,
        private readonly EntriesRepository $repository,
        private readonly TokenContainer $token_container,
    ) {
        $this->translator = $pons->i18n();
        $this->ui_factory = $pons->out()->ui()->factory();
        $this->url_builder = $this->token_container->builder();
        $this->id_token = $this->token_container->token();
        $this->request = $pons->in()->request();
        $this->translations_repository = $pons->i18n()->ml()->repository();
    }


    public function getRows(OrderingRowBuilder $row_builder, array $visible_column_ids): \Generator
    {
        $ok = $this->pons->out()->ok();
        $nok = $this->pons->out()->nok();

        foreach ($this->repository->allForParent($this->group->getId()) as $entry) {
            $title = $this->translations_repository->get($entry)->getDefault()?->getTranslation() ?? $entry->getTitle();
            $row = $row_builder->buildOrderingRow(
                $this->hash($entry->getId()),
                [
                    self::COLUMN_TITLE => $title,
                    self::COLUMN_ACTIVE => $entry->isActive() ? $ok : $nok,
                ]
            );

            if ($entry->isCore()) {
                $row = $row->withDisabledAction('delete')
                           ->withDisabledAction('edit')
                           ->withDisabledAction('translate')
                           ->withDisabledAction('move');
            }

            yield $row;
        }
    }

    public function get(): Ordering
    {
        $flow = $this->pons->flow();
        return $this->ui_factory
            ->table()
            ->ordering(
                $this,
                $flow->getHereAsURI(\ilFooterEntriesGUI::CMD_SAVE_ORDER),
                $this->group->getTitle(),
                [
                    self::COLUMN_TITLE => $this->ui_factory->table()->column()->text(
                        $this->translator->translate('title', 'entry')
                    ),
                    self::COLUMN_ACTIVE => $this->ui_factory->table()->column()->statusIcon(
                        $this->translator->translate('active', 'entry')
                    )
                ],
            )
            ->withRequest($this->request)
            ->withActions(
                [
                    'edit' => $this->ui_factory->table()->action()->single(
                        $this->translator->translate('edit', 'entry'),
                        $this->url_builder->withURI($flow->getHereAsURI('edit')),
                        $this->id_token
                    )->withAsync(true),

                    'toggle_activation' => $this->ui_factory->table()->action()->standard(
                        $this->translator->translate('toggle_activation', 'entry'),
                        $this->url_builder->withURI($flow->getHereAsURI('toggleActivation')),
                        $this->id_token
                    )->withAsync(false),

                    'delete' => $this->ui_factory->table()->action()->standard(
                        $this->translator->translate('delete', 'entry'),
                        $this->url_builder->withURI($flow->getHereAsURI('confirmDelete')),
                        $this->id_token
                    )->withAsync(true),

                    'move' => $this->ui_factory->table()->action()->standard(
                        $this->translator->translate('move', 'entry'),
                        $this->url_builder->withURI($flow->getHereAsURI('selectMove')),
                        $this->id_token
                    )->withAsync(true),

                    'translate' => $this->ui_factory->table()->action()->single(
                        $this->translator->translate('translate', 'group'),
                        $this->url_builder->withURI(
                            $flow->getTranslationAsURI()
                        ),
                        $this->id_token
                    )->withAsync(true),
                ]
            );
    }


}
