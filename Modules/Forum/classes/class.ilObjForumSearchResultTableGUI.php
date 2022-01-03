<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilObjForumSearchResultTableGUI extends ilRepositoryObjectSearchResultTableGUI
{
    public function parse() : void
    {
        global $DIC;

        $valid_threads = ilForum::getSortedThreadSubjects($DIC['ilObjDataCache']->lookupObjId($this->ref_id));

        $rows = [];
        foreach ($this->getResults()->getResults() as $result_set) {
            if (!array_key_exists($result_set['item_id'], $valid_threads)) {
                continue;
            }

            $row = [];

            $row['title'] = $valid_threads[$result_set['item_id']];

            $DIC->ctrl()->setParameterByClass(ilObjForumGUI::class, 'thr_pk', $result_set['item_id']);
            $row['link'] = $DIC->ctrl()->getLinkTargetByClass(ilObjForumGUI::class, 'viewThread');

            $row['relevance'] = $result_set['relevance'];
            $row['content'] = $result_set['content'];

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
