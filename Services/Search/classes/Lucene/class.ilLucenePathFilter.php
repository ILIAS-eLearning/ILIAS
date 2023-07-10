<?php

declare(strict_types=1);

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
 * Lucene path filter
 *
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesSearch
 */
class ilLucenePathFilter implements ilLuceneResultFilter
{
    protected int $root;
    protected array $subnodes = [];
    protected ilTree $tree;

    public function __construct(int $a_root)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->root = $a_root;
    }

    /**
     * Return whether a object reference is valid or not
     */
    public function filter(int $a_ref_id): bool
    {
        if ($this->root == ROOT_FOLDER_ID) {
            return true;
        }
        if ($this->root == $a_ref_id) {
            return true;
        }
        return $this->tree->isGrandChild($this->root, $a_ref_id);
    }

    /**
     * Read valid reference ids
     * @return void
     */
    protected function init(): void
    {
        if ($this->root == ROOT_FOLDER_ID) {
            $this->subnodes = array();
        } else {
            $node = $this->tree->getNodeData($this->root);
            $this->subnodes = $this->tree->getSubTree($node, false);
        }
    }
}
