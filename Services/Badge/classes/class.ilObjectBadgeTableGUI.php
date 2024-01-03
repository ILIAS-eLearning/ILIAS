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

use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use ILIAS\Badge\Tile;

/**
 * TableGUI class for badge listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjectBadgeTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected array $filter = [];
    private readonly Tile $tile;
    private readonly Renderer $ui_renderer;
    private readonly Factory $ui_factory;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd = "",
        protected bool $has_write = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tile = new Tile($DIC);
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->setId("bdgobdg");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setLimit(9999);

        $this->setTitle($lng->txt("badge_object_badges"));

        if ($this->has_write) {
            $this->addColumn("", "", 1);
        }

        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("type"), "type");
        $this->addColumn($lng->txt("object"), "container");
        $this->addColumn($lng->txt("active"), "active");
        $this->addColumn($lng->txt("action"), "");

        if ($this->has_write) {
            $this->addMultiCommand("activateObjectBadges", $lng->txt("activate"));
            $this->addMultiCommand("deactivateObjectBadges", $lng->txt("deactivate"));
            $this->addMultiCommand("confirmDeleteObjectBadges", $lng->txt("delete"));
            $this->setSelectAllCheckbox("id");
        }

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.object_badge_row.html", "Services/Badge");
        $this->setDefaultOrderField("title");

        $this->setFilterCommand("applyObjectFilter");
        $this->setResetCommand("resetObjectFilter");

        $this->initFilter();

        $this->getItems();
    }

    public function initFilter(): void
    {
        $lng = $this->lng;

        $title = $this->addFilterItemByMetaType("title", self::FILTER_TEXT, false, $lng->txt("title"));
        $this->filter["title"] = $title->getValue();

        $object = $this->addFilterItemByMetaType("object", self::FILTER_TEXT, false, $lng->txt("object"));
        $this->filter["object"] = $object->getValue();

        $lng->loadLanguageModule("search");

        $options = array(
            "" => $lng->txt("search_any"),
        );
        foreach (ilBadgeHandler::getInstance()->getAvailableTypes() as $id => $type) {
            // no activity badges
            if (!in_array("bdga", $type->getValidObjectTypes(), true)) {
                $options[$id] = ilBadge::getExtendedTypeCaption($type);
            }
        }
        asort($options);

        $type = $this->addFilterItemByMetaType("type", self::FILTER_SELECT, false, $lng->txt("type"));
        $type->setOptions($options);
        $this->filter["type"] = $type->getValue();
    }

    public function getItems(): void
    {
        $data = [];

        $types = ilBadgeHandler::getInstance()->getAvailableTypes(false);

        foreach (ilBadge::getObjectInstances($this->filter) as $badge_item) {
            $type_caption = ilBadge::getExtendedTypeCaption($types[$badge_item['type_id']]);

            $data[] = [
                'id' => (int) $badge_item['id'],
                'active' => $badge_item['active'],
                'type' => $type_caption,
                'title' => $badge_item['title'],
                'container' => $badge_item['parent_title'],
                'container_deleted' => (bool) ($badge_item['deleted'] ?? false),
                'container_id' => (int) $badge_item['parent_id'],
                'container_type' => $badge_item['parent_type'],
                'renderer' => fn () => $this->tile->asTitle(
                    $this->tile->modalContent(new ilBadge((int) $badge_item['id']))
                )
            ];
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        $container_parts = [
            $this->ui_renderer->render($this->ui_factory->symbol()->icon()->custom(
                ilObject::_getIcon($a_set['container_id'], 'big', $a_set['container_type']),
                $lng->txt('obj_' . $a_set['container_type'])
            )),
            $a_set['container'],
        ];

        $container_url = '';
        if ($a_set['container_deleted'] ?? false) {
            $container_parts[] = ' <span class="il_ItemAlertProperty">' . $lng->txt('deleted') . '</span>';
        } else {
            $ref_ids = ilObject::_getAllReferences($a_set['container_id']);
            $ref_id = array_shift($ref_ids);
            if ($ilAccess->checkAccess('read', '', $ref_id)) {
                $container_url = ilLink::_getLink($ref_id);
            }
        }

        $containter_info = implode(' ', $container_parts);
        if ($container_url !== '') {
            $this->tpl->setCurrentBlock('container_link_bl');
            $this->tpl->setVariable('TXT_CONTAINER', $containter_info);
            $this->tpl->setVariable('URL_CONTAINER', $container_url);
        } else {
            $this->tpl->setCurrentBlock('container_nolink_bl');
            $this->tpl->setVariable('TXT_CONTAINER_STATIC', $containter_info);
        }
        $this->tpl->parseCurrentBlock();

        if ($this->has_write) {
            $this->tpl->setVariable('VAL_ID', $a_set['id']);
        }

        $this->tpl->setVariable('PREVIEW', $this->ui_renderer->render($a_set['renderer']()));
        $this->tpl->setVariable('TXT_TYPE', $a_set['type']);
        $this->tpl->setVariable(
            'TXT_ACTIVE',
            $a_set['active'] ? $lng->txt('yes') : $lng->txt('no')
        );

        if ($this->has_write) {
            $ilCtrl->setParameter($this->getParentObject(), 'pid', $a_set['container_id']);
            $ilCtrl->setParameter($this->getParentObject(), 'bid', $a_set['id']);
            $url = $ilCtrl->getLinkTarget($this->getParentObject(), 'listObjectBadgeUsers');
            $ilCtrl->setParameter($this->getParentObject(), 'bid', '');
            $ilCtrl->setParameter($this->getParentObject(), 'pid', '');

            $this->tpl->setVariable('TXT_LIST', $lng->txt('users'));
            $this->tpl->setVariable('URL_LIST', $url);
        }
    }
}
