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

/**
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjLearningModuleSubItemListGUI extends ilSubItemListGUI
{
    protected ilObjUser $user;
    
    public function __construct(string $a_cmd_class)
    {
        global $DIC;
        parent::__construct($a_cmd_class);

        $this->user = $DIC->user();
    }

    
    public function getHTML() : string
    {
        $lng = $this->lng;

        foreach ($this->getSubItemIds(true) as $sub_item) {
            if (is_object($this->getHighlighter()) and strlen($this->getHighlighter()->getContent($this->getObjId(), $sub_item))) {
                $this->tpl->setCurrentBlock('sea_fragment');
                $this->tpl->setVariable('TXT_FRAGMENT', $this->getHighlighter()->getContent($this->getObjId(), $sub_item));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('subitem');

            $this->tpl->setVariable('SEPERATOR', ':');
            
            
            switch (ilLMObject::_lookupType($sub_item, $this->getObjId())) {
                case 'pg':
                    $this->getItemListGUI()->setChildId($sub_item);
                    $this->tpl->setVariable("SUBITEM_TYPE", $lng->txt('obj_pg'));
                    $link = $this->getItemListGUI()->getCommandLink('page');
                    $link .= ('&srcstring=1');
                    $this->tpl->setVariable('LINK', $link);
                    $this->tpl->setVariable('TARGET', $this->getItemListGUI()->getCommandFrame('page'));
                    $this->tpl->setVariable('TITLE', ilLMObject::_lookupTitle($sub_item));
                    break;
                    
                case 'st':
                    
                    $this->getItemListGUI()->setChildId($sub_item);
                    $this->tpl->setVariable("SUBITEM_TYPE", $lng->txt('obj_st'));
                    $link = $this->getItemListGUI()->getCommandLink('page');
                    $link .= ('&srcstring=1');
                    $this->tpl->setVariable('LINK', $link);
                    $this->tpl->setVariable('TARGET', $this->getItemListGUI()->getCommandFrame('page'));
                    $this->tpl->setVariable('TITLE', ilLMObject::_lookupTitle($sub_item));
                    break;

                default:

                    if (ilObject::_lookupType($sub_item) != 'file') {
                        return '';
                    }
                    
                    $this->getItemListGUI()->setChildId('il__file_' . $sub_item);
                    $this->tpl->setVariable('SUBITEM_TYPE', $lng->txt('obj_file'));
                    $link = $this->getItemListGUI()->getCommandLink('downloadFile');
                    $this->tpl->setVariable('LINK', $link);
                    $this->tpl->setVariable('TITLE', ilObject::_lookupTitle($sub_item));
                    break;
            }

            if (count($this->getSubItemIds(true)) > 1) {
                $this->parseRelevance($sub_item);
            }
            
            $this->tpl->parseCurrentBlock();
        }
        
        $this->showDetailsLink();
        
        return $this->tpl->get();
    }
}
