<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilLTIConsumerProviderSelectionFormGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerProviderUsageTableGUI extends ilTable2GUI
{

    /**
     * @var ilLTIConsumerProviderUsageTableGUI
     */
    protected $table;

    /**
     * ilLTIConsumerProviderUsageTableGUI constructor.
     * @param ilLTIConsumerAdministrationGUI $a_parent_obj
     * @param $a_parent_cmd
     */
    public function __construct(ilLTIConsumerAdministrationGUI $a_parent_obj, $a_parent_cmd)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->setId('usages');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        //$this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate('tpl.lti_consume_provider_usage_table_row.html', 'Modules/LTIConsumer');

        $this->setTitle($DIC->language()->txt('tbl_provider_usage_header'));
        $this->setDescription($DIC->language()->txt('tbl_provider_usage_header_info'));
    }

    /*
    public function getTitle()
    {
        return $this->title;
    }
    */
    public function init()
    {
        parent::determineSelectedColumns();
        $this->initColumns();
    }

    protected function initColumns()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->addColumn($DIC->language()->txt('tbl_lti_prov_icon'), 'icon');
        $this->addColumn($DIC->language()->txt('tbl_lti_prov_title'), 'title');
        $this->addColumn($DIC->language()->txt('tbl_lti_prov_usages_trashed'), 'usedByIsTrashed');
        $this->addColumn($DIC->language()->txt('tbl_lti_prov_used_by'), 'used_by');
    }

    protected function fillRow($data)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        // TITLE
        $this->tpl->setVariable('TITLE', $data['title']);

        // TRASHED
        $this->tpl->setCurrentBlock('usages_trashed');
        $usagesTrashed = $data['usedByIsTrashed'] && $this->isTrashEnabled() ? $DIC->language()->txt('yes') : '';
        $this->tpl->setVariable('USAGES_TRASHED', $usagesTrashed);
        $this->tpl->parseCurrentBlock();

        // USED BY
        $this->tpl->setCurrentBlock('used_by');
        $tree = $this->buildLinkToUsedBy($data['usedByObjId'], $data['usedByRefId'], (string)$data['usedByTitle'], (bool)$usagesTrashed);
        $this->tpl->setVariable('TREE_TO_USED_BY', $tree['tree']);
        $this->tpl->parseCurrentBlock();

        // ICON
        if( $data['icon'] )
        {
            $this->tpl->setVariable('ICON_SRC', $data['icon']);
            $this->tpl->setVariable('ICON_ALT', basename($data['icon']));
        }
        else
        {
            $icon = ilObject::_getIcon("", "small", "lti");
            $this->tpl->setVariable('ICON_SRC', $icon);
            $this->tpl->setVariable('ICON_ALT', 'lti');
        }



    }

    protected function buildLinkToUsedBy(int $objId, int $refId, string $title, $trashed)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $tree = $DIC->repositoryTree()->getPathFull($refId);
        $treeNodes = [];
        foreach($tree as $node) {
            $node['title'] = (int)$node['parent'] === 0 ? $DIC->language()->txt('repository') : $node['title'];
            $treeNodes[] = $trashed === true ? $node['title'] : '<a href="'. ilLink::_getLink($node['ref_id']) .'">' . $node['title']. '</a>';
        }
        $endnode = '<a href="'. ilLink::_getLink($refId) .'">' . $title . '</a>';
        if( $trashed === true ) {
            $treeNodes[] = $title;
        }

        return ['endnode' => $endnode, 'tree' => implode(' > ', $treeNodes)];
    }

    protected static function isTrashEnabled()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        return (bool)$DIC->settings()->get('enable_trash', 0);
    }


} // EOF class