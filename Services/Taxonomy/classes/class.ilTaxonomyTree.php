<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tree/classes/class.ilTree.php");

/**
 * Taxonomy tree
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesTaxonomy
 */
class ilTaxonomyTree extends ilTree
{
	function __construct($a_id)
	{
		parent::__construct($a_id);	// only one skill tree, with ID 1
		$this->setTreeTablePK("tax_tree_id");
		$this->setTableNames('tax_tree', 'tax_node');
	}
}

?>
