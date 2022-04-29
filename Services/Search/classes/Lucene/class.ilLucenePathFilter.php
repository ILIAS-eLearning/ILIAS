<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Lucene path filter
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
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
    public function filter(int $a_ref_id) : bool
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
    protected function init() : void
    {
        if ($this->root == ROOT_FOLDER_ID) {
            $this->subnodes = array();
        } else {
            $node = $this->tree->getNodeData($this->root);
            $this->subnodes = $this->tree->getSubTree($node, false);
        }
    }
}
