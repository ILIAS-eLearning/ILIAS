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

use ILIAS\UI\Renderer;
use ILIAS\DI\UIServices;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\UI\Component\Table\OrderingRetrieval;
use ILIAS\UI\Component\Table\OrderingRowBuilder;
use ILIAS\GlobalScreen\GUI\Hasher;
use ILIAS\GlobalScreen\GUI\Input\TokenContainer;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilMMSubItemTableComponent implements OrderingRetrieval
{
    use Hasher;

    /**
     * @var string
     */
    public const ACTION_EDIT = 'edit';
    /**
     * @var string
     */
    public const ACTION_TRANSLATE = 'translate';
    /**
     * @var string
     */
    public const ACTION_ACTIVATE = 'activate';
    /**
     * @var string
     */
    public const ACTION_DEACTIVATE = 'deactivate';
    /**
     * @var string
     */
    public const ACTION_MOVE = 'move';
    /**
     * @var string
     */
    public const ACTION_DELETE = 'delete';
    private UIServices $ui;
    private ?URLBuilder $url_builder = null;
    private ?URLBuilderToken $token = null;
    private Factory $ui_factory;
    private Renderer $ui_renderer;

    public function __construct(
        private Pons $pons,
        TokenContainer $token_container,
        private ilMMItemRepository $repository,
        private ?ilMMItemFacadeInterface $parent_item,
        private bool $write_access
    ) {
        $this->ui_factory = $this->pons->out()->ui()->factory();
        $this->ui_renderer = $this->pons->out()->ui()->renderer();
        $this->url_builder = $token_container->builder();
        $this->token = $token_container->token();
    }

    public function getRows(OrderingRowBuilder $row_builder, array $visible_column_ids): Generator
    {
        if ($this->parent_item === null) {
            $items = $this->repository->getLostItems();
        } else {
            $items = $this->repository->getSubItemsForTable($this->parent_item);
        }

        foreach ($items as $sub_item) {
            $id = $sub_item['identification'];
            $item = $this->repository->getItemFacade(
                $this->repository->resolveIdentificationFromString($id)
            );

            $remark = $item->getStatus() !== null ? $this->ui_renderer->render($item->getStatus()) : '';

            if (preg_match('/^-\w+-$/', $remark)) {
                // this is a language variable, translate it
                $remark = $this->pons->i18n()->t(substr($remark, 1, -1));
            }

            yield $row_builder->buildOrderingRow(
                $this->hash($id),
                [
                    'title' => $item->getDefaultTitle(),
                    'active' => $item->isActivated(),
                    'status' => $remark,
                    'type' => $item->getTypeForPresentation(),
                    'provider' => $item->getProviderNameForPresentation(),
                ]
            )->withDisabledAction(
                self::ACTION_ACTIVATE,
                !$this->write_access || $item->isActivated()
            )->withDisabledAction(
                self::ACTION_DEACTIVATE,
                !$this->write_access || !$item->isActivated(),
            )->withDisabledAction(
                self::ACTION_MOVE,
                !$this->write_access || !$item->isInterchangeable()
            )->withDisabledAction(
                self::ACTION_DELETE,
                !$this->write_access || !$item->isCustom()
            )->withDisabledAction(
                self::ACTION_TRANSLATE,
                !$this->write_access
            )->withDisabledAction(
                self::ACTION_EDIT,
                !$this->write_access
            )->withDisabledAction(
                self::ACTION_DEACTIVATE,
                !$item->canBeDeactivated(),
            );
        }
    }

    public function get(): Component|array
    {
        if ($this->write_access) {
            $actions = [
                self::ACTION_EDIT => $this->ui_factory->table()->action()->single(
                    $this->pons->i18n()->t('edit'),
                    $this->url_builder->withURI(
                        $this->pons->flow()->getHereAsURI(ilMMSubItemGUI::CMD_EDIT)
                    ),
                    $this->token
                )->withAsync(true),
                self::ACTION_TRANSLATE => $this->ui_factory->table()->action()->single(
                    $this->pons->i18n()->t('translate'),
                    $this->url_builder->withURI(
                        $this->pons->flow()->getTranslationAsURI()
                    ),
                    $this->token
                )->withAsync(true),
                self::ACTION_ACTIVATE => $this->ui_factory->table()->action()->standard(
                    $this->pons->i18n()->t('activate'),
                    $this->url_builder->withURI(
                        $this->pons->flow()->getHereAsURI(ilMMSubItemGUI::CMD_ACTIVATE)
                    ),
                    $this->token
                ),
                self::ACTION_DEACTIVATE => $this->ui_factory->table()->action()->standard(
                    $this->pons->i18n()->t('deactivate'),
                    $this->url_builder->withURI(
                        $this->pons->flow()->getHereAsURI(ilMMSubItemGUI::CMD_DEACTIVATE)
                    ),
                    $this->token
                ),
                self::ACTION_MOVE => $this->ui_factory->table()->action()->standard(
                    $this->pons->i18n()->t('move'),
                    $this->url_builder->withURI(
                        $this->pons->flow()->getHereAsURI(ilMMSubItemGUI::CMD_CONFIRM_MOVE)
                    ),
                    $this->token
                )->withAsync(true),
                self::ACTION_DELETE => $this->ui_factory->table()->action()->standard(
                    $this->pons->i18n()->t('delete'),
                    $this->url_builder->withURI(
                        $this->pons->flow()->getHereAsURI(ilMMSubItemGUI::CMD_CONFIRM_DELETE)
                    ),
                    $this->token
                )->withAsync(true),
            ];
        } else {
            $actions = [];
        }
        return [
            $this
                ->ui_factory
                ->table()
                ->ordering(
                    $this,
                    $this->pons->flow()->getHereAsURI(ilMMSubItemGUI::CMD_SAVE_ORDER),
                    $this->parent_item?->getDefaultTitle() ?? $this->pons->i18n()->t('mme_lost_items'),
                    [
                        'title' => $this->ui_factory->table()->column()->text(
                            $this->pons->i18n()->t('title', 'sub'),
                        ),
                        'active' => $this->ui_factory->table()->column()->boolean(
                            $this->pons->i18n()->t('active', 'sub'),
                            $this->pons->out()->ok(),
                            $this->pons->out()->nok(),
                        ),
                        'status' => $this->ui_factory->table()->column()->text(
                            $this->pons->i18n()->t('status', 'sub'),
                        ),
                        'type' => $this->ui_factory->table()->column()->text(
                            $this->pons->i18n()->t('type', 'topitem'),
                        ),
                        'provider' => $this->ui_factory->table()->column()->text(
                            $this->pons->i18n()->t('provider', 'topitem'),
                        ),
                    ]
                )->withRequest(
                    $this->pons->in()->request()
                )
                ->withOrderingDisabled(!$this->write_access)
                ->withActions($actions)
        ];
    }
}
