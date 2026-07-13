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

namespace ILIAS\GlobalScreen\UI\Footer\Groups;

use ILIAS\GlobalScreen\Scope\Footer\Collector\FooterMainCollector;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\UI\Factory;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table\Ordering;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\UI\Component\Table\OrderingRetrieval;
use ILIAS\UI\Component\Table\OrderingRowBuilder;
use ILIAS\GlobalScreen\GUI\I18n\Translator;
use ILIAS\GlobalScreen\GUI\Input\TokenContainer;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\GlobalScreen\UI\Footer\Translation\TranslationsRepositoryDB;

class GroupsTable implements OrderingRetrieval
{
    use Hasher;

    public const COLUMN_ACTIVE = 'active';
    public const COLUMN_TITLE = 'title';
    public const CLUMNS_ITEMS = 'items';
    private Factory $ui_factory;
    private Translator $translator;
    private ServerRequestInterface $request;
    private TranslationsRepositoryDB $translations_repository;
    private ?URLBuilderToken $id_token = null;
    private ?URLBuilder $url_builder = null;
    private FooterMainCollector $collector;
    private IdentificationFactory $identification;

    public function __construct(
        private Pons $pons,
        private readonly GroupsRepository $repository,
        private readonly TokenContainer $token_container,
    ) {
        global $DIC;

        $this->translator = $pons->i18n();
        $this->ui_factory = $pons->out()->ui()->factory();
        $this->url_builder = $this->token_container->builder();
        $this->id_token = $this->token_container->token();
        $this->request = $pons->in()->request();
        $this->translations_repository = $pons->i18n()->ml()->repository();
        $this->collector = $DIC->globalScreen()->collector()->footer();
        $this->identification = $DIC->globalScreen()->identification();
    }

    public function getRows(OrderingRowBuilder $row_builder, array $visible_column_ids): \Generator
    {
        $ok = $this->pons->out()->ok();
        $nok = $this->pons->out()->nok();
        $edit_entries = $this->pons->flow()->getTargetURI(\ilFooterEntriesGUI::class, \ilFooterEntriesGUI::CMD_DEFAULT);

        $write_access = $this->pons->access()->hasUserPermissionTo('write');

        foreach ($this->repository->all() as $group) {
            if ($group->isCore()) {
                $title = $this->collector->getSingleItemFromRaw(
                    $this->identification->fromSerializedIdentification($group->getId()),
                )?->getTitle() ?? 'Unknown';
            } else {
                $title = $this->translations_repository->get($group)->getDefault()?->getTranslation(
                ) ?? $group->getTitle();
            }
            $row = $row_builder->buildOrderingRow(
                $this->hash($group->getId()),
                [
                    self::COLUMN_TITLE => $this->ui_factory->link()->standard(
                        $title,
                        $this->url_builder
                            ->withURI($edit_entries)
                            ->withParameter($this->id_token, $this->hash($group->getId()))
                            ->buildURI()
                    ),
                    self::COLUMN_ACTIVE => $group->isActive() ? $ok : $nok,
                    self::CLUMNS_ITEMS => $group->getItems(),
                ]
            );

            if (!$write_access || $group->isCore()) {
                $row = $row->withDisabledAction('delete')
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
                $flow->getHereAsURI(\ilFooterGroupsGUI::CMD_SAVE_ORDER),
                $this->translator->translate('groups'),
                [
                    self::COLUMN_TITLE => $this->ui_factory->table()->column()->link(
                        $this->translator->translate('title', 'group')
                    ),
                    self::COLUMN_ACTIVE => $this->ui_factory->table()->column()->statusIcon(
                        $this->translator->translate('active', 'group')
                    ),
                    self::CLUMNS_ITEMS => $this->ui_factory->table()->column()->text(
                        $this->translator->translate('items', 'group')
                    ),
                ],
            )
            ->withRequest($this->request)
            ->withActions(
                [
                    'edit_entries' => $this->ui_factory->table()->action()->single(
                        $this->translator->translate('edit_entries', 'group'),
                        $this->url_builder->withURI(
                            $flow->getTargetURI(\ilFooterEntriesGUI::class, \ilFooterEntriesGUI::CMD_DEFAULT)
                        ),
                        $this->id_token
                    )->withAsync(false),

                    'edit' => $this->ui_factory->table()->action()->single(
                        $this->translator->translate('edit', 'group'),
                        $this->url_builder->withURI($flow->getHereAsURI(\ilFooterGroupsGUI::CMD_EDIT)),
                        $this->id_token
                    )->withAsync(true),

                    'toggle_activation' => $this->ui_factory->table()->action()->standard(
                        $this->translator->translate('toggle_activation', 'group'),
                        $this->url_builder->withURI($flow->getHereAsURI(\ilFooterGroupsGUI::CMD_TOGGLE_ACTIVATION)),
                        $this->id_token
                    )->withAsync(false),

                    'delete' => $this->ui_factory->table()->action()->standard(
                        $this->translator->translate('delete', 'group'),
                        $this->url_builder->withURI($flow->getHereAsURI(\ilFooterGroupsGUI::CMD_CONFIRM_DELETE)),
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
