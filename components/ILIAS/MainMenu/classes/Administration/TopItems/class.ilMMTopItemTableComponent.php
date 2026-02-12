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
class ilMMTopItemTableComponent implements OrderingRetrieval
{
    use Hasher;

    /**
     * @var string
     */
    public const ACTION_EDIT_SUB_TEMS = 'edit_sub_tems';
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
    private ?URLBuilder $url_builder = null;
    private ?URLBuilderToken $token = null;
    private Factory $ui_factory;
    private Renderer $ui_renderer;

    public function __construct(
        private Pons $pons,
        TokenContainer $token_container,
        private ilMMItemRepository $repository,
        private bool $write_access
    ) {
        $this->ui_factory = $this->pons->out()->ui()->factory();
        $this->ui_renderer = $this->pons->out()->ui()->renderer();
        $this->url_builder = $token_container->builder();
        $this->token = $token_container->token();
    }

    public function getRows(OrderingRowBuilder $row_builder, array $visible_column_ids): Generator
    {
        foreach ($this->repository->getTopItems() as $top_item) {
            $id = $top_item['identification'];
            $item = $this->repository->getItemFacade(
                $this->repository->resolveIdentificationFromString($id)
            );

            $link = $this->ui_factory->link()->standard(
                $item->getDefaultTitle(),
                (string) $this->url_builder
                    ->withURI(
                        $this->pons->flow()->getTargetURI(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_DEFAULT)
                    )
                    ->withParameter($this->token, $this->hash($id))
                    ->buildURI()
            )->withDisabled(
                !$item->canHaveChildren()
            );

            $remark = $item->getStatus() !== null ? $this->ui_renderer->render($item->getStatus()) : '';

            if (preg_match('/-[^-]*-/', $remark)) {
                // this is a language variable, translate it
                $substr = substr($remark, 1, -1);
                $remark = $this->pons->i18n()->t($substr);
            }

            yield $row_builder->buildOrderingRow(
                $this->hash($id),
                [
                    'title' => $link,
                    'active' => $item->isActivated(),
                    'status' => $remark,
                    'sub_items' => ($item->canHaveChildren() ? $item->getAmountOfChildren() : '-'),
                    'css_id' => "mm_" . $item->identification()->getInternalIdentifier(),
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
                self::ACTION_EDIT,
                !$this->write_access
            )->withDisabledAction(
                self::ACTION_TRANSLATE,
                !$this->write_access
            )->withDisabledAction(
                self::ACTION_EDIT_SUB_TEMS,
                !$item->canHaveChildren()
            )->withDisabledAction(
                self::ACTION_DEACTIVATE,
                !$item->canBeDeactivated(),
            );
        }

        // Lost Items
        if ($this->repository->hasLostItems()) {
            yield $row_builder->buildOrderingRow(
                $this->hash('lost_items'),
                [
                    'title' => $this->ui_factory->link()->standard(
                        $this->pons->i18n()->t('mme_lost_items'),
                        (string) $this->url_builder
                            ->withURI(
                                $this->pons->flow()->getTargetURI(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_DEFAULT)
                            )
                            ->withParameter($this->token, $this->hash('lost_items'))
                            ->buildURI()
                    ),
                    'active' => false,
                    'status' => '',
                    'sub_items' => '-',
                    'css_id' => "mm_lost_items",
                    'type' => '-',
                    'provider' => '-',
                ]
            )->withDisabledAction(
                self::ACTION_EDIT,
                true
            )->withDisabledAction(
                self::ACTION_TRANSLATE,
                true
            )->withDisabledAction(
                self::ACTION_ACTIVATE,
                true
            )->withDisabledAction(
                self::ACTION_DEACTIVATE,
                true,
            )->withDisabledAction(
                self::ACTION_MOVE,
                true
            )->withDisabledAction(
                self::ACTION_DELETE,
                true
            )->withDisabledAction(
                self::ACTION_EDIT_SUB_TEMS,
                true
            );

        }

    }

    public function get(): Component|array
    {
        $actions = [
            self::ACTION_EDIT_SUB_TEMS => $this->ui_factory->table()->action()->single(
                $this->pons->i18n()->t('edit_sub_tems'),
                $this->url_builder->withURI(
                    $this->pons->flow()->getTargetURI(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_DEFAULT)
                ),
                $this->token
            ),
        ];
        if ($this->write_access) {
            $actions = array_merge($actions, [
                self::ACTION_EDIT => $this->ui_factory->table()->action()->single(
                    $this->pons->i18n()->t('edit'),
                    $this->url_builder->withURI(
                        $this->pons->flow()->getHereAsURI(ilMMTopItemGUI::CMD_EDIT)
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
                        $this->pons->flow()->getHereAsURI(ilMMTopItemGUI::CMD_ACTIVATE)
                    ),
                    $this->token
                ),
                self::ACTION_DEACTIVATE => $this->ui_factory->table()->action()->standard(
                    $this->pons->i18n()->t('deactivate'),
                    $this->url_builder->withURI(
                        $this->pons->flow()->getHereAsURI(ilMMTopItemGUI::CMD_DEACTIVATE)
                    ),
                    $this->token
                ),
                self::ACTION_MOVE => $this->ui_factory->table()->action()->standard(
                    $this->pons->i18n()->t('move'),
                    $this->url_builder->withURI(
                        $this->pons->flow()->getHereAsURI(ilMMTopItemGUI::CMD_SELECT_PARENT)
                    ),
                    $this->token
                )->withAsync(true),
                self::ACTION_DELETE => $this->ui_factory->table()->action()->standard(
                    $this->pons->i18n()->t('delete'),
                    $this->url_builder->withURI(
                        $this->pons->flow()->getHereAsURI(ilMMTopItemGUI::CMD_CONFIRM_DELETE)
                    ),
                    $this->token
                )->withAsync(true),
            ]);
        }

        return [
            $this->ui_factory
                ->table()
                ->ordering(
                    $this,
                    $this->pons->flow()->getHereAsURI(ilMMTopItemGUI::CMD_SAVE_ORDER),
                    $this->pons->i18n()->t('subtab_topitems'),
                    [
                        'title' => $this->ui_factory->table()->column()->link(
                            $this->pons->i18n()->t('title', 'topitem'),
                        ),
                        'active' => $this->ui_factory->table()->column()->boolean(
                            $this->pons->i18n()->t('active', 'topitem'),
                            $this->pons->out()->ok(),
                            $this->pons->out()->nok(),
                        ),
                        'status' => $this->ui_factory->table()->column()->text(
                            $this->pons->i18n()->t('status', 'sub')
                        ),
                        'sub_items' => $this->ui_factory->table()->column()->text(
                            $this->pons->i18n()->t('subentries', 'topitem'),
                        ),
                        'type' => $this->ui_factory->table()->column()->text(
                            $this->pons->i18n()->t('type', 'topitem'),
                        ),
                        'provider' => $this->ui_factory->table()->column()->text(
                            $this->pons->i18n()->t('provider', 'topitem'),
                        ),
                    ]
                )
                ->withOrderingDisabled(!$this->write_access)
                ->withRequest($this->pons->in()->request())
                ->withActions(
                    $actions
                )
        ];
    }
}
