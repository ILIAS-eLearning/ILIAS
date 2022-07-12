<?php declare(strict_types=1);

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
 
/**
 * TableGUI class for search results
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesWebResource
 */
class ilWebResourceLinkTableGUI extends ilTable2GUI
{
    protected bool $editable = false;
    protected ilLinkResourceItems $webresource_items;

    protected int $link_sort_mode;
    protected bool $link_sort_enabled = false;

    protected ilAccessHandler $access;

    public function __construct(
        ?object $a_parent_obj,
        string $a_parent_cmd,
        bool $a_sorting = false
    ) {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->access = $DIC->access();

        // Initialize
        if ($this->access->checkAccess(
            'write',
            '',
            $this->getParentObject()->getObject()->getRefId()
        )) {
            $this->editable = true;
        }

        $this->enableLinkSorting($a_sorting);
        $this->webresource_items = new ilLinkResourceItems(
            $this->getParentObject()->getObject()->getId()
        );

        $this->setTitle($this->lng->txt('web_resources'));

        if ($this->isEditable()) {
            if ($this->isLinkSortingEnabled()) {
                $this->setLimit(9999);
                $this->addColumn($this->lng->txt('position'), '', '10px');
                $this->addColumn($this->lng->txt('title'), '', '90%');
                $this->addColumn('', '', '10%');

                $this->addMultiCommand(
                    'saveSorting',
                    $this->lng->txt('sorting_save')
                );
            } else {
                $this->addColumn($this->lng->txt('title'), '', '90%');
                $this->addColumn('', '', '10%');
            }
        } else {
            $this->addColumn($this->lng->txt('title'), '', '100%');
        }

        $this->initSorting();

        $this->setEnableHeader(true);
        $this->setFormAction(
            $this->ctrl->getFormAction($this->getParentObject())
        );
        $this->setRowTemplate("tpl.webr_link_row.html", 'Modules/WebResource');
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(false);
    }

    public function enableLinkSorting(bool $a_status) : void
    {
        $this->link_sort_enabled = $a_status;
    }

    public function isLinkSortingEnabled() : bool
    {
        return $this->link_sort_enabled;
    }

    public function parse() : void
    {
        $rows = [];

        $items = $this->getWebResourceItems()->getActivatedItems();
        $items = $this->getWebResourceItems()->sortItems($items);

        $counter = 1;
        foreach ($items as $link) {
            $tmp['position'] = ($counter++) * 10;
            $tmp['title'] = $link['title'];
            $tmp['description'] = $link['description'];
            $tmp['target'] = $link['target'];
            $tmp['link_id'] = $link['link_id'];
            $tmp['internal'] = ilLinkInputGUI::isInternalLink($link["target"]);

            $rows[] = $tmp;
        }
        $this->setData($rows);
    }

    protected function fillRow(array $a_set) : void
    {
        $this->ctrl->setParameterByClass(
            get_class($this->getParentObject()),
            'link_id',
            $a_set['link_id']
        );

        $this->tpl->setVariable('TITLE', $a_set['title']);
        if (strlen($a_set['description']) !== 0) {
            $this->tpl->setVariable('DESCRIPTION', $a_set['description']);
        }
        // $this->tpl->setVariable('TARGET',$a_set['target']);
        $this->tpl->setVariable(
            'TARGET',
            $this->ctrl->getLinkTarget($this->parent_obj, "callLink")
        );

        if (!$a_set['internal']) {
            $this->tpl->setVariable('FRAME', ' target="_blank"');
            $this->tpl->touchBlock('noopener');
        }

        if (!$this->isEditable()) {
            return;
        }

        if ($this->isLinkSortingEnabled()) {
            $this->tpl->setVariable('VAL_POS', $a_set['position']);
            $this->tpl->setVariable('VAL_ITEM', $a_set['link_id']);
        }

        $actions = new ilAdvancedSelectionListGUI();
        $actions->setSelectionHeaderClass("small");
        $actions->setItemLinkClass("xsmall");

        $actions->setListTitle($this->lng->txt('actions'));
        $actions->setId((string) $a_set['link_id']);

        $actions->addItem(
            $this->lng->txt('edit'),
            '',
            $this->ctrl->getLinkTargetByClass(
                get_class($this->getParentObject()),
                'editLink'
            )
        );
        $actions->addItem(
            $this->lng->txt('webr_deactivate'),
            '',
            $this->ctrl->getLinkTargetByClass(
                get_class($this->getParentObject()),
                'deactivateLink'
            )
        );
        $actions->addItem(
            $this->lng->txt('delete'),
            '',
            $this->ctrl->getLinkTargetByClass(
                get_class($this->getParentObject()),
                'confirmDeleteLink'
            )
        );
        $this->tpl->setVariable('ACTION_HTML', $actions->getHTML());
    }

    /**
     * Get Web resource items object
     * @return object    ilLinkResourceItems
     */
    protected function getWebResourceItems() : \ilLinkResourceItems
    {
        return $this->webresource_items;
    }

    /**
     * Check if links are editable
     * @return
     */
    protected function isEditable() : bool
    {
        return $this->editable;
    }

    protected function initSorting() : void
    {
        $this->link_sort_mode = ilContainerSortingSettings::_lookupSortMode(
            $this->getParentObject()->getObject()->getId()
        );
    }
}
