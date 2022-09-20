<?php

declare(strict_types=1);

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

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;

/**
 * Class ilMMSubItemTableGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMSubItemTableGUI extends ilTable2GUI
{
    use Hasher;

    public const IDENTIFIER = 'identifier';
    public const F_TABLE_SHOW_INACTIVE = 'table_show_inactive';
    public const F_TABLE_ENTRY_STATUS = 'entry_status';
    public const F_TABLE_ALL_VALUE = 1;
    public const F_TABLE_ONLY_ACTIVE_VALUE = 2;
    public const F_TABLE_ONLY_INACTIVE_VALUE = 3;

    private ilObjMainMenuAccess $access;

    private array $filter;
    private ilMMItemRepository $item_repository;

    /**
     * ilMMSubItemTableGUI constructor.
     * @param ilMMSubItemGUI     $a_parent_obj
     * @param ilMMItemRepository $item_repository
     */
    public function __construct(
        ilMMSubItemGUI $a_parent_obj,
        ilMMItemRepository $item_repository,
        ilObjMainMenuAccess $access
    ) {
        $this->access = $access;
        $this->setId(self::class);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        parent::__construct($a_parent_obj);
        $this->item_repository = $item_repository;
        $this->lng = $this->parent_obj->lng;
        $this->addFilterItems();
        $this->setData($this->resolveData());
        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
        if ($this->access->hasUserPermissionTo('write')) {
            $this->addCommandButton(ilMMSubItemGUI::CMD_SAVE_TABLE, $this->lng->txt('button_save'));
        }
        $this->initColumns();
        $this->setRowTemplate('tpl.sub_items.html', 'Services/MainMenu');
    }

    protected function addFilterItems(): void
    {
        $table_entry_status = new ilSelectInputGUI(
            $this->lng->txt(self::F_TABLE_ENTRY_STATUS),
            self::F_TABLE_ENTRY_STATUS
        );
        $table_entry_status->setOptions(
            array(
                self::F_TABLE_ALL_VALUE => $this->lng->txt("all"),
                self::F_TABLE_ONLY_ACTIVE_VALUE => $this->lng->txt("only_active"),
                self::F_TABLE_ONLY_INACTIVE_VALUE => $this->lng->txt("only_inactive"),
            )
        );
        $this->addAndReadFilterItem($table_entry_status);
    }

    protected function addAndReadFilterItem(ilFormPropertyGUI $field): void
    {
        if (!$field instanceof ilTableFilterItem) {
            return;
        }
        $this->addFilterItem($field);
        $field->readFromSession();
        if ($field instanceof ilCheckboxInputGUI) {
            $this->filter[$field->getPostVar()] = $field->getChecked();
        } else {
            $this->filter[$field->getPostVar()] = $field->getValue();
        }
    }

    private function initColumns(): void
    {
        $this->addColumn($this->lng->txt('sub_parent'));
        $this->addColumn($this->lng->txt('sub_position'));
        $this->addColumn($this->lng->txt('sub_title'));
        $this->addColumn($this->lng->txt('sub_type'));
        $this->addColumn($this->lng->txt('sub_active'));
        $this->addColumn($this->lng->txt('sub_status'));
        $this->addColumn($this->lng->txt('sub_provider'));
        $this->addColumn($this->lng->txt('sub_actions'));
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set): void
    {
        static $position;
        static $parent_identification_string;
        $position++;
        global $DIC;

        $renderer = $DIC->ui()->renderer();
        $factory = $DIC->ui()->factory();
        /**
         * @var $item_facade ilMMItemFacadeInterface
         */
        $item_facade = $a_set['facade'];

        if ($item_facade->isChild()) {
            if (!$parent_identification_string ||
                $parent_identification_string !== $item_facade->getParentIdentificationString()) {
                $parent_identification_string = $item_facade->getParentIdentificationString();
                $current_parent_identification = $this->item_repository->resolveIdentificationFromString(
                    $parent_identification_string
                );
                $current_parent_item = $this->item_repository->getSingleItem($current_parent_identification);
                $this->tpl->setVariable(
                    "PARENT_TITLE",
                    $current_parent_item instanceof hasTitle ? $current_parent_item->getTitle() : "-"
                );
                $this->tpl->setVariable(
                    "NATIVE_PARENT_ID",
                    $current_parent_item->getProviderIdentification()->serialize()
                );
                $this->tpl->setVariable(
                    "PARENT_ID",
                    $this->hash($current_parent_item->getProviderIdentification()->serialize())
                );
                $position = 1;
            }
        }
        $this->tpl->setVariable('IDENTIFIER', self::IDENTIFIER);
        $this->tpl->setVariable('ID', $this->hash($item_facade->getId()));
        $this->tpl->setVariable('NATIVE_ID', $item_facade->getId());
        $this->tpl->setVariable('TITLE', $item_facade->getDefaultTitle());
        $this->tpl->setVariable('PARENT', $this->getSelect($item_facade)->render());
        $this->tpl->setVariable('STATUS', $item_facade->getStatus());
        if ($item_facade->isActivated()) {
            $this->tpl->touchBlock('is_active');
        }
        if ($item_facade->getRawItem()->isAlwaysAvailable() || !$item_facade->getRawItem()->isAvailable()) {
            $this->tpl->touchBlock('is_active_blocked');
        }

        $this->tpl->setVariable('POSITION', $position * 10);
        $this->tpl->setVariable('NATIVE_POSITION', $item_facade->getRawItem()->getPosition());
        $this->tpl->setVariable('SAVED_POSITION', $item_facade->getFilteredItem()->getPosition());
        $this->tpl->setVariable('TYPE', $item_facade->getTypeForPresentation());
        $this->tpl->setVariable('PROVIDER', $item_facade->getProviderNameForPresentation());

        $this->ctrl->setParameterByClass(
            ilMMSubItemGUI::class,
            ilMMAbstractItemGUI::IDENTIFIER,
            $this->hash($a_set['identification'])
        );
        $this->ctrl->setParameterByClass(
            ilMMItemTranslationGUI::class,
            ilMMItemTranslationGUI::IDENTIFIER,
            $this->hash($a_set['identification'])
        );

        if ($this->access->hasUserPermissionTo('write')) {
            $items[] = $factory->button()->shy(
                $this->lng->txt(ilMMSubItemGUI::CMD_EDIT),
                $this->ctrl->getLinkTargetByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_EDIT)
            );
            $items[] = $factory->button()->shy(
                $this->lng->txt(ilMMTopItemGUI::CMD_TRANSLATE),
                $this->ctrl->getLinkTargetByClass(ilMMItemTranslationGUI::class, ilMMItemTranslationGUI::CMD_DEFAULT)
            );

            $ditem = $factory->modal()->interruptiveItem(
                $this->hash($a_set['identification']),
                $item_facade->getDefaultTitle()
            );

            $delete_modal = "";
            if ($item_facade->isDeletable()) {
                $action = $this->ctrl->getFormActionByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_DELETE);
                $m = $factory->modal()
                             ->interruptive(
                                 $this->lng->txt(ilMMSubItemGUI::CMD_DELETE),
                                 $this->lng->txt(ilMMSubItemGUI::CMD_CONFIRM_DELETE),
                                 $action
                             )
                             ->withAffectedItems([$ditem]);

                $items[] = $factory->button()->shy(
                    $this->lng->txt(ilMMSubItemGUI::CMD_DELETE),
                    ""
                )->withOnClick($m->getShowSignal());
                $delete_modal = $renderer->render([$m]);
            }

            $move_modal = "";
            if ($item_facade->isInterchangeable()) {
                $action = $this->ctrl->getFormActionByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_MOVE);
                $m = $factory->modal()
                             ->interruptive(
                                 $this->lng->txt(ilMMSubItemGUI::CMD_MOVE),
                                 $this->lng->txt(ilMMSubItemGUI::CMD_CONFIRM_MOVE),
                                 $action
                             )
                             ->withActionButtonLabel(ilMMSubItemGUI::CMD_MOVE)
                             ->withAffectedItems([$ditem]);
                $items[] = $factory->button()->shy(
                    $this->lng->txt(ilMMSubItemGUI::CMD_MOVE . '_to_top_item'),
                    ""
                )->withOnClick($m->getShowSignal());
                $move_modal = $renderer->render([$m]);
            }

            $this->tpl->setVariable(
                'ACTIONS',
                $move_modal . $delete_modal . $renderer->render([$factory->dropdown()->standard($items)->withLabel($this->lng->txt('sub_actions'))])
            );
        }
    }

    /**
     * @param ilMMItemFacadeInterface $child
     * @return ilSelectInputGUI
     */
    private function getSelect(ilMMItemFacadeInterface $child): ilSelectInputGUI
    {
        $s = new ilSelectInputGUI('', self::IDENTIFIER . "[{$this->hash($child->getId())}][parent]");
        $s->setOptions($this->getPossibleParentsForFormAndTable());
        $s->setValue($this->hash($child->getParentIdentificationString()));

        return $s;
    }

    /**
     * @return array
     */
    public function getPossibleParentsForFormAndTable(): array
    {
        $parents = [];
        foreach ($this->item_repository->getPossibleParentsForFormAndTable() as $identification => $name) {
            $parents[$this->hash($identification)] = $name;
        }

        return $parents;
    }

    private function resolveData(): array
    {
        global $DIC;
        $sub_items_for_table = $this->item_repository->getSubItemsForTable();

        // populate with facade
        array_walk($sub_items_for_table, function (&$item) use ($DIC) {
            $item_ident = $DIC->globalScreen()->identification()->fromSerializedIdentification($item['identification']);
            $item_facade = $this->item_repository->repository()->getItemFacade($item_ident);
            $item['facade'] = $item_facade;
        });

        // filter active/inactive
        array_filter($sub_items_for_table, function ($item_facade) {
            if (!isset($this->filter[self::F_TABLE_ENTRY_STATUS])) {
                return true;
            }
            if ($this->filter[self::F_TABLE_ENTRY_STATUS] !== self::F_TABLE_ALL_VALUE) {
                return true;
            }
            if ($this->filter[self::F_TABLE_ENTRY_STATUS] == self::F_TABLE_ONLY_ACTIVE_VALUE && !$item_facade->isActivated()) {
                return false;
            }
            if ($this->filter[self::F_TABLE_ENTRY_STATUS] == self::F_TABLE_ONLY_INACTIVE_VALUE && $item_facade->isActivated()) {
                return false;
            }
            return true;
        });

        return $sub_items_for_table;
    }
}
