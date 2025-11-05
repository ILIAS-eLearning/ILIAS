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

use ILIAS\ItemGroup\InternalGUIService;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component\Link\Standard;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * Item group items table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilItemGroupItemsTableGUI extends ilTable2GUI
{
    protected array $items;
    protected ilItemGroupItems $item_group_items;
    protected ilTree $tree;
    protected ilObjectDefinition $obj_def;
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;

    public function __construct(
        protected readonly InternalGUIService $gui,
        ilObjItemGroupGUI $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tree = $DIC->repositoryTree();
        $this->obj_def = $DIC['objDefinition'];
        $this->ui_factory = $this->gui->ui()->factory();
        $this->ui_renderer = $this->gui->ui()->renderer();

        $this->item_group_items = new ilItemGroupItems($a_parent_obj->getObject()?->getRefId() ?? 0);
        $this->items = $this->item_group_items->getItems();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setLimit(9999);

        $this->loadMaterials();
        $this->setTitle($this->lng->txt('itgr_assigned_materials'));

        $this->addColumn('', '', '1px', true);
        $this->addColumn($this->lng->txt('itgr_item'));
        $this->addColumn($this->lng->txt('itgr_assignment'));
        $this->addColumn($this->lng->txt('itgr_assignment_to_other_itgr'));
        $this->setSelectAllCheckbox('items[]');

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate('tpl.item_group_items_row.html', 'components/ILIAS/ItemGroup');

        $this->addCommandButton('saveItemAssignment', $this->lng->txt('save'));
    }

    protected function loadMaterials(): void
    {
        $materials = [];
        foreach ($this->item_group_items->getAssignableItems() as $item) {
            $item['sorthash'] = (int) (!in_array($item['ref_id'], $this->items, true)) . $item['title'];
            $item['assigned_items'] = ilItemGroupItems::getItemGroupsAssociatedWithItem(
                $item['ref_id'],
                $this->getParentObject()?->getObject()->getId() ?? 0
            );
            $materials[] = $item;
        }

        $this->setData(ilArrayUtil::sortArray($materials, 'sorthash', 'asc'));
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('ITEM_REF_ID', $a_set['child']);
        $this->tpl->setVariable('TITLE', $a_set['title']);
        $this->tpl->setVariable('IMG', ilUtil::img(
            ilObject::_getIcon((int) $a_set['obj_id'], 'tiny'),
            '',
            '',
            '',
            '',
            '',
            'ilIcon'
        ));

        $assigned_items = array_map(
            function (int $ref_id, string $assigned_item): Standard {
                $this->ctrl->setParameter($this->getParentObject(), 'ref_id', $ref_id);
                $link = $this->ui_factory->link()->standard($assigned_item, $this->ctrl->getLinkTarget($this->getParentObject(), 'listMaterials'));
                $this->ctrl->setParameter($this->getParentObject(), 'ref_id', null);
                return $link;
            },
            array_keys($a_set['assigned_items']),
            $a_set['assigned_items']
        );
        $this->tpl->setVariable('ASSIGNED_LIST', $this->ui_renderer->render($this->ui_factory->listing()->unordered($assigned_items)));

        if (in_array($a_set['child'], $this->items, true)) {
            $i = $this->ui_factory->symbol()->icon()->custom(
                ilUtil::getImagePath('standard/icon_ok.svg'),
                $this->lng->txt('yes')
            );
            $this->tpl->setVariable('CHECKED', 'checked="checked"');
        } else {
            $i = $this->ui_factory->symbol()->icon()->custom(
                ilUtil::getImagePath('standard/icon_not_ok.svg'),
                $this->lng->txt('no')
            );
        }

        $this->tpl->setVariable('IMG_ASSIGNED', $this->ui_renderer->render($i));
    }
}
