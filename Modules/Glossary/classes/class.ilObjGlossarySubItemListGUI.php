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
 * Show glossary terms
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjGlossarySubItemListGUI extends ilSubItemListGUI
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
        $ilUser = $this->user;
        
        $lng->loadLanguageModule('content');
        foreach ($this->getSubItemIds(true) as $sub_item) {
            if (is_object($this->getHighlighter()) && $this->getHighlighter()->getContent($this->getObjId(), $sub_item) !== '') {
                $this->tpl->setCurrentBlock('sea_fragment');
                $this->tpl->setVariable('TXT_FRAGMENT', $this->getHighlighter()->getContent($this->getObjId(), $sub_item));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('subitem');
            $this->tpl->setVariable('SUBITEM_TYPE', $lng->txt('cont_term'));
            $this->tpl->setVariable('SEPERATOR', ':');
            
            $src_string = ilUserSearchCache::_getInstance($ilUser->getId())->getUrlEncodedQuery();
            
            $this->tpl->setVariable('LINK', ilLink::_getLink(
                $this->getRefId(),
                'git',
                array(
                    'target' => 'git_' . $sub_item . '_' . $this->getRefId(),
                    'srcstring' => 1
                )
            ));
            
            $this->tpl->setVariable('TARGET', $this->getItemListGUI()->getCommandFrame(''));
            $this->tpl->setVariable('TITLE', ilGlossaryTerm::_lookGlossaryTerm($sub_item));

            // begin-patch mime_filter
            if (count($this->getSubItemIds(true)) > 1) {
                $this->parseRelevance($sub_item);
            }
            // end-patch mime_filter
            
            $this->tpl->parseCurrentBlock();
        }
        
        $this->showDetailsLink();
        
        return $this->tpl->get();
    }
}
