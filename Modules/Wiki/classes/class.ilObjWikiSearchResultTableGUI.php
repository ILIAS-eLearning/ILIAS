<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Search/classes/class.ilRepositoryObjectSearchResultTableGUI.php';

/**
 * Wiki search result table
 *
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @package ModulesWiki
 */
class ilObjWikiSearchResultTableGUI extends ilRepositoryObjectSearchResultTableGUI
{
    /**
     * Parse results and call setDatea
     */
    public function parse()
    {
        $ilCtrl = $this->ctrl;
        
        $rows = array();
        foreach ($this->getResults()->getResults() as $result_set) {
            $row = array();
            include_once './Modules/Wiki/classes/class.ilWikiPage.php';
            $row['title']  = ilWikiPage::lookupTitle($result_set['item_id']);
            
            $ilCtrl->setParameterByClass(
                'ilwikipagegui',
                'page',
                ilWikiUtil::makeUrlTitle($row['title'])
            );
            $row['link'] = $ilCtrl->getLinkTargetByClass('ilwikipagegui', 'preview');
            
            $row['relevance'] = $result_set['relevance'];
            $row['content'] = $result_set['content'];
            
            $rows[] = $row;
        }
        
        $this->setData($rows);
    }
    
    /**
     * Fill result row
     * @param type $a_set
     */
    public function fillRow($a_set)
    {
        $this->tpl->setVariable('HREF_ITEM', $a_set['link']);
        $this->tpl->setVariable('TXT_ITEM_TITLE', $a_set['title']);
        
        if ($this->getSettings()->enabledLucene()) {
            $this->tpl->setVariable('RELEVANCE', $this->getRelevanceHTML($a_set['relevance']));
        }
        if (strlen($a_set['content'])) {
            $this->tpl->setVariable('HIGHLIGHT_CONTENT', $a_set['content']);
        }
    }
}
