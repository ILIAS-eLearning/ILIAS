<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* check data
* Validates, cleans up object registry and may recover lost objects
* THIS SCRIPT IS EXPERIMENTAL!! YOU MAY USE THIS TOOL FOR ANALYZING YOUR DATA
* BUT DO NOT ACTIVATE THE RECOVERY MODE UNTIL THIS MODE IS FINISHED!!
* @author	Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @package tools
*/
require_once "./include/inc.header.php";
require_once "./classes/class.ilValidator.php";

$validator = new ilValidator();

//$tree->insertNode(227,15);
//$par_node = $tree->getParentNodeData(225);
//vd("parent_node",$par_node);
// check logical tree constistenx
//$tree->checkTree();

// check if every entry (child) has a corresponding entry in object_data or reference_data
// if referenced, checks too if at least one corresponding object_data entry exists_
//$tree->checkTreeChilds();

//vd($rbac_obj_types);
// check for missing reference entries
//$objs_no_tree = $validator->getMissingTreeEntries();
//vd("missing_in_tree",$objs_no_tree);


// general clean up first

// remove unbound references
$unbound_refs = $validator->getUnboundedReferences();
vd("unbound_refs",$unbound_refs);

$refs_removed = $validator->removeUnboundedReferences($unbound_refs);

if ($refs_removed)
{
	echo "references removed";
}

// remove unbound tree entries (childs without any reference)
$unbound_childs = $validator->getUnboundedChilds();
vd("unbound_childs",$unbound_childs);

$childs_removed = $validator->removeUnboundedChilds($unbound_childs);

if ($childs_removed)
{
	echo "childs removed";
}

// create category containing recovered objects
$objRecover = $validator->getRecoveryFolder();


// save unbounded objects and childs with invalid parent to category '__recovered'
$objs_no_ref = $validator->getMissingObjects();
vd("missing_overall",$objs_no_ref);

$objs_restored = $validator->restoreMissingObjects($objRecover,$objs_no_ref);

if ($objs_restored)
{
	echo "Missing objects restored";
}

// restore childs with invalid parents
$childs_invalid_parent = $validator->getChildsWithInvalidParents();
vd("invalid_parents",$childs_invalid_parent);

$childs_restored = $validator->restoreUnboundedChilds($objRecover,$childs_invalid_parent);

if ($childs_restored)
{
	echo "Unbounded childs restored";
}

// check RBAC starts here
// ...


// el fin
$mode = ($validator->recover) ? "Recovered Objects!" : "Analyze only!";

$tpl->setVariable("CONTENT", "Tree ok. (Mode: ".$mode.")");
$tpl->show()
?>
