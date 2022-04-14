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
 * @author Nadia Matuschek <nmatuschek@databay.de>
 * Class ilForumPostsTree
 */
class ilForumPostsTree
{
    private ilDBInterface $db;
    private int $pos_fk = 0;
    private int $parent_pos = 0;
    private int $lft = 0;
    private int $rgt = 0;
    private int $depth = 0;
    private int $source_thread_id = 0;
    private int $target_thread_id = 0;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
    }

    public function setDepth(int $depth) : void
    {
        $this->depth = $depth;
    }

    public function getDepth() : int
    {
        return $this->depth;
    }

    public function setLft(int $lft) : void
    {
        $this->lft = $lft;
    }

    public function getLft() : int
    {
        return $this->lft;
    }

    public function setParentPos(int $parent_pos) : void
    {
        $this->parent_pos = $parent_pos;
    }

    public function getParentPos() : int
    {
        return $this->parent_pos;
    }

    public function setPosFk(int $pos_fk) : void
    {
        $this->pos_fk = $pos_fk;
    }

    public function getPosFk() : int
    {
        return $this->pos_fk;
    }

    public function setRgt(int $rgt) : void
    {
        $this->rgt = $rgt;
    }

    public function getRgt() : int
    {
        return $this->rgt;
    }

    public function setSourceThreadId(int $source_thread_id) : void
    {
        $this->source_thread_id = $source_thread_id;
    }

    public function getSourceThreadId() : int
    {
        return $this->source_thread_id;
    }

    public function setTargetThreadId(int $target_thread_id) : void
    {
        $this->target_thread_id = $target_thread_id;
    }

    public function getTargetThreadId() : int
    {
        return $this->target_thread_id;
    }

    public function merge() : void
    {
        $this->db->update(
            'frm_posts_tree',
            [
                'lft' => ['integer', $this->getLft()],
                'rgt' => ['integer', $this->getRgt()],
                'depth' => ['integer', $this->getDepth()],
                'thr_fk' => ['integer', $this->getTargetThreadId()],
                'parent_pos' => ['integer', $this->getParentPos()],
            ],
            [
                'pos_fk' => ['integer', $this->getPosFk()],
                'thr_fk' => ['integer', $this->getSourceThreadId()]
            ]
        );
    }

    public static function updateTargetRootRgt(int $root_node_id, int $rgt) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->update(
            'frm_posts_tree',
            [
                'rgt' => ['integer', $rgt]
            ],
            [
                'parent_pos' => ['integer', 0],
                'pos_fk' => ['integer', $root_node_id]
            ]
        );
    }
}
