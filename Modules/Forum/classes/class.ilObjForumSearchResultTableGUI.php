<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjForumSearchResultTableGUI
 */
class ilObjForumSearchResultTableGUI extends ilRepositoryObjectSearchResultTableGUI
{
    /**
     * Parse results and call setData
     */
    public function parse()
    {
        global $DIC;

        $valid_threads = ilForum::_getThreads($DIC['ilObjDataCache']->lookupObjId($this->ref_id));

        $rows = array();
        foreach ($this->getResults()->getResults() as $result_set) {
            if (!array_key_exists($result_set['item_id'], $valid_threads)) {
                continue;
            }

            $row = array();

            $row['title'] = $valid_threads[$result_set['item_id']];

            $DIC->ctrl()->setParameterByClass('ilObjForumGUI', 'thr_pk', $result_set['item_id']);
            $row['link'] = $DIC->ctrl()->getLinkTargetByClass('ilObjForumGUI', 'viewThread');

            $row['relevance'] = $result_set['relevance'];
            $row['content'] = $result_set['content'];

            $rows[] = $row;
        }

        $this->setData($rows);
    }

    /**
     * Fill result row
     * @param array $a_set
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
