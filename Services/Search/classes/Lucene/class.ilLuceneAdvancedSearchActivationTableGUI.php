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
* Activation of meta data fields
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup
*/
class ilLuceneAdvancedSearchActivationTableGUI extends ilTable2GUI
{
    protected ilAccess $access;

    public function __construct($a_parent_obj, $a_parent_cmd = '')
    {
        global $DIC;

        $this->access = $DIC->access();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn('', 'id', '0px');
        $this->addColumn($this->lng->txt('title'), 'title', '60%');
        $this->addColumn($this->lng->txt('type'), 'type', '40%');
        $this->setRowTemplate('tpl.lucene_activation_row.html', 'Services/Search');
        $this->disable('sort');
        $this->setLimit(100);
        $this->setSelectAllCheckbox('fid');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));

        if ($this->access->checkAccess('write', '', $this->getParentObject()->getObject()->getRefId())) {
            $this->addMultiCommand('saveAdvancedLuceneSettings', $this->lng->txt('lucene_activate_field'));
        }
    }
    
    /**
     * Fill template row
     */
    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_CHECKED', $a_set['active'] ? 'checked="checked"' : '');
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        $this->tpl->setVariable('VAL_TYPE', $a_set['type']);
    }
    
    public function parse(ilLuceneAdvancedSearchSettings $settings) : void
    {
        $content = [];
        foreach (ilLuceneAdvancedSearchFields::getFields() as $field => $translation) {
            $tmp_arr['id'] = $field;
            $tmp_arr['active'] = $settings->isActive($field);
            $tmp_arr['title'] = $translation;
            
            $tmp_arr['type'] = (substr($field, 0, 3) == 'lom') ?
                $this->lng->txt('search_lom') :
                $this->lng->txt('search_adv_md');
            
            $content[] = $tmp_arr;
        }
        $this->setData($content);
    }
}
