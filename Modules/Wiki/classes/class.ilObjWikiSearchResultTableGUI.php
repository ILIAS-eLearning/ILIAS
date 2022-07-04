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
 * Wiki search result table
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjWikiSearchResultTableGUI extends ilRepositoryObjectSearchResultTableGUI
{
    public function parse() : void
    {
        $ilCtrl = $this->ctrl;
        
        $rows = array();
        foreach ($this->getResults()->getResults() as $result_set) {
            $row = array();
            $row['title'] = ilWikiPage::lookupTitle($result_set['item_id']);
            
            $ilCtrl->setParameterByClass(
                'ilwikipagegui',
                'page',
                ilWikiUtil::makeUrlTitle($row['title'])
            );
            $row['link'] = $ilCtrl->getLinkTargetByClass('ilwikipagegui', 'preview');
            
            $row['relevance'] = (float) ($result_set['relevance'] ?? 0);
            $row['content'] = $result_set['content'] ?? "";
            
            $rows[] = $row;
        }
        
        $this->setData($rows);
    }
    
    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('HREF_ITEM', $a_set['link']);
        $this->tpl->setVariable('TXT_ITEM_TITLE', $a_set['title']);
        
        if ($this->getSettings()->enabledLucene()) {
            $this->tpl->setVariable('RELEVANCE', $this->getRelevanceHTML($a_set['relevance']));
        }

        if ($a_set['content'] !== '') {
            $this->tpl->setVariable('HIGHLIGHT_CONTENT', $a_set['content']);
        }
    }
}
