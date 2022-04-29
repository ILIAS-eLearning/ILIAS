<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    protected ilLTIConsumerProviderUsageTableGUI $table;

    /**
     * ilLTIConsumerProviderUsageTableGUI constructor.
     * @param $a_parent_cmd
     */
    public function __construct(ilLTIConsumerAdministrationGUI $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->setId('usages');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        //$this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate('tpl.lti_consume_provider_usage_table_row.html', 'Modules/LTIConsumer');

        $this->setTitle($DIC->language()->txt('tbl_provider_usage_header'));
        $this->setDescription($DIC->language()->txt('tbl_provider_usage_header_info'));
    }

    public function init() : void
    {
        parent::determineSelectedColumns();
        $this->initColumns();
    }

    protected function initColumns() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->addColumn($DIC->language()->txt('tbl_lti_prov_icon'), 'icon');
        $this->addColumn($DIC->language()->txt('tbl_lti_prov_title'), 'title');
        $this->addColumn($DIC->language()->txt('tbl_lti_prov_usages_trashed'), 'usedByIsTrashed');
        $this->addColumn($DIC->language()->txt('tbl_lti_prov_used_by'), 'used_by');
    }

    protected function fillRow(array $a_set) : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        // TITLE
        $this->tpl->setVariable('TITLE', $a_set['title']);

        // TRASHED
        $this->tpl->setCurrentBlock('usages_trashed');
        $usagesTrashed = $a_set['usedByIsTrashed'] && $this->isTrashEnabled() ? $DIC->language()->txt('yes') : '';
        $this->tpl->setVariable('USAGES_TRASHED', $usagesTrashed);
        $this->tpl->parseCurrentBlock();

        // USED BY
        $this->tpl->setCurrentBlock('used_by');
        $tree = $this->buildLinkToUsedBy($a_set['usedByObjId'], (int) $a_set['usedByRefId'], (string) $a_set['usedByTitle'], (bool) $usagesTrashed);
        $this->tpl->setVariable('TREE_TO_USED_BY', $tree['tree']);
        $this->tpl->parseCurrentBlock();

        // ICON
        if ($a_set['icon']) {
            $this->tpl->setVariable('ICON_SRC', $a_set['icon']);
            $this->tpl->setVariable('ICON_ALT', basename($a_set['icon']));
        } else {
            $icon = ilObject::_getIcon(0, "small", "lti");
            $this->tpl->setVariable('ICON_SRC', $icon);
            $this->tpl->setVariable('ICON_ALT', 'lti');
        }
    }

    /**
     * @return array<string, string>
     */
    protected function buildLinkToUsedBy(int $objId, int $refId, string $title, bool $trashed) : array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $tree = $DIC->repositoryTree()->getPathFull($refId);
        $treeNodes = [];
        foreach ($tree as $node) {
            $node['title'] = (int) $node['parent'] === 0 ? $DIC->language()->txt('repository') : $node['title'];
            $treeNodes[] = $trashed === true ? $node['title'] : '<a href="' . ilLink::_getLink($node['ref_id']) . '">' . $node['title'] . '</a>';
        }
        $endnode = '<a href="' . ilLink::_getLink($refId) . '">' . $title . '</a>';
        if ($trashed === true) {
            $treeNodes[] = $title;
        }

        return ['endnode' => $endnode, 'tree' => implode(' > ', $treeNodes)];
    }

    protected static function isTrashEnabled() : bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        return (bool) ((int) $DIC->settings()->get('enable_trash', "0"));
    }
} // EOF class
