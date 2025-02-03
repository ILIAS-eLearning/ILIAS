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

declare(strict_types=1);
/**
* Class ilLMContentSearch
*
* Abstract class for lm content.
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilForumSearch extends ilAbstractSearch
{
    public function performSearch(): ilSearchResult
    {
        // Search in topic titles, posting title, posting

        // First: search topics:
        $this->setFields(array('thr_subject'));

        $and = $this->__createTopicAndCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT thr_pk,top_frm_fk frm_id " .
            $locate .
            "FROM  frm_threads,frm_data " .
            "WHERE top_pk = thr_top_fk " .
            $and;

        $res = $this->db->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            #$thread_post = $row->thr_pk.'_0';
            $thread_post = $row->thr_pk;
            $this->search_result->addEntry(
                (int) $row->frm_id,
                'frm',
                $this->__prepareFound($row),
                (int) $thread_post
            );
        }

        // First: search post title, content:
        $this->setFields(array('pos_subject','pos_message'));

        $and = $this->__createPostAndCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT top_frm_fk frm_id,pos_thr_fk,pos_pk " .
            $locate .
            "FROM  frm_posts,frm_data " .
            "WHERE pos_top_fk = top_pk " .
            $and;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            #$thread_post = $row->pos_thr_fk.'_'.$row->pos_pk;
            $thread_post = $row->pos_thr_fk;
            $this->search_result->addEntry(
                (int) $row->frm_id,
                'frm',
                $this->__prepareFound($row),
                (int) $thread_post
            );
        }
        return $this->search_result;
    }
}
