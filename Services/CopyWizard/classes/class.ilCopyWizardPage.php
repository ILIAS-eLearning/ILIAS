<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesCopyWizard
*/
class ilCopyWizardPage
{
    private $type;
    private $source_id;
    private $obj_id;
    private $item_type;
    
    private $tree;
    private $lng;
    private $objDefinition;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct($a_source_id, $a_item_type = '')
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $tree = $DIC['tree'];
        $lng = $DIC['lng'];
        $objDefinition = $DIC['objDefinition'];
        
        $this->source_id = $a_source_id;
        $this->item_type = $a_item_type;
        $this->obj_id = $ilObjDataCache->lookupObjId($this->source_id);
        $this->type = $ilObjDataCache->lookupType($this->obj_id);
        $this->tree = $tree;
        $this->lng = $lng;
        $this->objDefinition = $objDefinition;
    }
    
    /**
     * Fill selection template
     *
     * @access public
     * @param int ref_id of node
     * @param string type of current node
     *
     */
    public function fillTreeSelection($a_ref_id, $a_type, $a_depth)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $ilAccess = $DIC['ilAccess'];
        
        $this->tpl = $tpl;
        
        $perm_copy = $ilAccess->checkAccess('copy', '', $a_ref_id);
        $copy = $this->objDefinition->allowCopy($a_type);
        $perm_link = $ilAccess->checkAccess('write', '', $a_ref_id);
        $link = $this->objDefinition->allowLink($a_type);
        
        // Show radio copy
        if ($perm_copy and $copy) {
            $this->tpl->setCurrentBlock('radio_copy');
            $this->tpl->setVariable('TXT_COPY', $this->lng->txt('copy'));
            $this->tpl->setVariable('NAME_COPY', 'cp_options[' . $a_ref_id . '][type]');
            $this->tpl->setVariable('VALUE_COPY', ilCopyWizardOptions::COPY_WIZARD_COPY);
            $this->tpl->setVariable('ID_COPY', $a_depth . '_' . $a_type . '_' . $a_ref_id . '_copy');
            $this->tpl->setVariable('COPY_CHECKED', 'checked="checked"');
            $this->tpl->parseCurrentBlock();
        } elseif ($copy) {
            $this->tpl->setCurrentBlock('missing_copy_perm');
            $this->tpl->setVariable('TXT_MISSING_COPY_PERM', $this->lng->txt('missing_perm'));
            $this->tpl->parseCurrentBlock();
        }
        
        // Show radio link
        if ($perm_link and $link) {
            $this->tpl->setCurrentBlock('radio_link');
            $this->tpl->setVariable('TXT_LINK', $this->lng->txt('link'));
            $this->tpl->setVariable('NAME_LINK', 'cp_options[' . $a_ref_id . '][type]');
            $this->tpl->setVariable('VALUE_LINK', ilCopyWizardOptions::COPY_WIZARD_LINK);
            $this->tpl->setVariable('ID_LINK', $a_depth . '_' . $a_type . '_' . $a_ref_id . '_link');
            if (!$copy or !$perm_copy) {
                $this->tpl->setVariable('LINK_CHECKED', 'checked="checked"');
            }
            $this->tpl->parseCurrentBlock();
        } elseif ($link) {
            $this->tpl->setCurrentBlock('missing_link_perm');
            $this->tpl->setVariable('TXT_MISSING_LINK_PERM', $this->lng->txt('missing_perm'));
            $this->tpl->parseCurrentBlock();
        }

        // Radio omit
        $this->tpl->setVariable('TXT_OMIT', $this->lng->txt('omit'));
        $this->tpl->setVariable('NAME_OMIT', 'cp_options[' . $a_ref_id . '][type]');
        $this->tpl->setVariable('VALUE_OMIT', ilCopyWizardOptions::COPY_WIZARD_OMIT);
        $this->tpl->setVariable('ID_OMIT', $a_depth . '_' . $a_type . '_' . $a_ref_id . '_omit');
        if (((!$copy or !$perm_copy) and (!$link or !$perm_link))) {
            $this->tpl->setVariable('OMIT_CHECKED', 'checked="checked"');
        }
    }
    

    
    /**
     * Get wizard page block html
     *
     * @access public
     *
     */
    public function getWizardPageBlockHTML()
    {
        $this->readItems();
        
        if (!count($this->items)) {
            return '';
        }
    }

    /**
     *
     *
     * @access protected
     */
    protected function fillMainBlock()
    {
        if (count($this->items) > 1) {
            $this->tpl->setCurrentBlock('obj_options');
            $this->tpl->setVariable('NAME_OPTIONS', $this->lng->txt('omit_all'));
            $this->tpl->setVariable('JS_FIELD', $this->item_type . '_' . ilCopyWizardOptions::COPY_WIZARD_OMIT);
            $this->tpl->setVariable('JS_TYPE', $this->item_type . '_omit');
            $this->tpl->parseCurrentBlock();
            
            $this->tpl->setCurrentBlock('obj_options');
            $this->tpl->setVariable('NAME_OPTIONS', $this->lng->txt('copy_all'));
            $this->tpl->setVariable('OBJ_CHECKED', 'checked="checked"');
            $this->tpl->setVariable('JS_FIELD', $this->item_type . '_' . ilCopyWizardOptions::COPY_WIZARD_COPY);
            $this->tpl->setVariable('JS_TYPE', $this->item_type . '_copy');
            $this->tpl->parseCurrentBlock();
    
            if ($this->objDefinition->allowLink($this->item_type)) {
                $this->tpl->setCurrentBlock('obj_options');
                $this->tpl->setVariable('NAME_OPTIONS', $this->lng->txt('link_all'));
                $this->tpl->setVariable('JS_FIELD', $this->item_type . '_' . ilCopyWizardOptions::COPY_WIZARD_LINK);
                $this->tpl->setVariable('JS_TYPE', $this->item_type . '_link');
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setVariable('OPTION_CLASS', 'option_value');
        } else {
            $this->tpl->setVariable('OPTION_CLASS', 'option');
        }
        $this->tpl->setVariable('OBJ_IMG', ilUtil::getImagePath('icon_' . $this->item_type . '.svg'));
        $this->tpl->setVariable('OBJ_ALT', $this->lng->txt('objs_' . $this->item_type));
        $this->tpl->setVariable('ROWSPAN', count($this->items) + 1);
    }
    
    /**
     * Fill item block
     *
     * @access protected
     */
    protected function fillItemBlock()
    {
        foreach ($this->items as $node) {
            $selected = $this->fetchSelected($node['child']);
            
            
            $this->tpl->setCurrentBlock('item_options');
            $this->tpl->setVariable('ITEM_CHECK_NAME', 'cp_options[' . $node['child'] . '][type]');
            $this->tpl->setVariable('ITEM_VALUE', ilCopyWizardOptions::COPY_WIZARD_OMIT);
            $this->tpl->setVariable('ITEM_NAME_OPTION', $this->lng->txt('omit'));
            if ($selected == ilCopyWizardOptions::COPY_WIZARD_OMIT) {
                $this->tpl->setVariable('ITEM_CHECKED', 'checked="checked"');
            }
            $this->tpl->setVariable('ITEM_ID', $this->item_type . '_' . ilCopyWizardOptions::COPY_WIZARD_OMIT);
            $this->tpl->parseCurrentBlock();
            
            
            if ($this->objDefinition->allowCopy($this->item_type)) {
                $this->tpl->setCurrentBlock('item_options');
                if ($selected == ilCopyWizardOptions::COPY_WIZARD_COPY) {
                    $this->tpl->setVariable('ITEM_CHECKED', 'checked="checked"');
                }
                $this->tpl->setVariable('ITEM_CHECK_NAME', 'cp_options[' . $node['child'] . '][type]');
                $this->tpl->setVariable('ITEM_VALUE', ilCopyWizardOptions::COPY_WIZARD_COPY);
                $this->tpl->setVariable('ITEM_NAME_OPTION', $this->lng->txt('copy'));
                $this->tpl->setVariable('ITEM_ID', $this->item_type . '_' . ilCopyWizardOptions::COPY_WIZARD_COPY);
                $this->tpl->parseCurrentBlock();
            }
            if ($this->objDefinition->allowLink($this->item_type)) {
                $this->tpl->setCurrentBlock('item_options');
                if ($selected == ilCopyWizardOptions::COPY_WIZARD_LINK) {
                    $this->tpl->setVariable('ITEM_CHECKED', 'checked="checked"');
                }
                $this->tpl->setVariable('ITEM_CHECK_NAME', 'cp_options[' . $node['child'] . '][type]');
                $this->tpl->setVariable('ITEM_VALUE', ilCopyWizardOptions::COPY_WIZARD_LINK);
                $this->tpl->setVariable('ITEM_NAME_OPTION', $this->lng->txt('link'));
                $this->tpl->setVariable('ITEM_ID', $this->item_type . '_' . ilCopyWizardOptions::COPY_WIZARD_LINK);
                $this->tpl->parseCurrentBlock();
            }
            
            
            $this->tpl->setCurrentBlock('item_row');
            $this->tpl->setVariable('ITEM_TITLE', $node['title']);
            $this->tpl->setVariable('DESCRIPTION', $node['description']);
            $this->tpl->parseCurrentBlock();
        }
    }
    
    /**
     * Fill additional options
     *
     * @access protected
     */
    protected function fillAdditionalOptions()
    {
    }
    
    /**
     * Read items
     *
     * @access protected
     */
    protected function readItems()
    {
        $nodes = $this->tree->getSubTree($this->tree->getNodeData($this->source_id), true, $this->item_type);
        
        $this->items = array();
        switch ($nodes[0]['type']) {
            case 'fold':
            case 'grp':
            case 'crs':
            case 'cat':
                foreach ($nodes as $node) {
                    if ($node['child'] != $this->source_id) {
                        $this->items[] = $node;
                    }
                }
                break;
            default:
                $this->items = $nodes;
                break;
        }
    }
    
    /**
     * Check if it is checked
     *
     * @access protected
     */
    protected function fetchSelected($a_node_id)
    {
        return $_POST['cp_options'][$a_node_id]['type'] ?
            $_POST['cp_options'][$a_node_id]['type'] :
            ilCopyWizardOptions::COPY_WIZARD_COPY;
    }
}
