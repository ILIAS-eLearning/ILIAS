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
* ACTIVATING THE RECOVERY MODE ON YOUR OWN RISK!!!

* @author	Sascha Hofmann <saschahofmann@gmx.de>
* @version	$Id$
*
* @package	ilias-tools
*/
require_once "./include/inc.header.php";
require_once "./classes/class.ilValidator.php";

// error codes
//define("INVALID_PARAM",INVALID_PARAM);

$validator = new ilValidator();
$validator->setMode("all",true);

// STEP 1: Analyzing: Get all incomplete entries
echo "<br/><br/>Analyzing...";

echo "<br/>Search unbound references...";
if ($validator->findUnboundReferences())
{
	echo "done (".count($validator->getUnboundReferences())." found).";
}
else
{
	echo "nothing found.";
}

echo "<br/>Search for unbound tree entries...";
if ($validator->findUnboundChilds())
{
	echo "done (".count($validator->getUnboundChilds())." found).";
}
else
{
	echo "nothing found.";
}

echo "<br/>Search for missing objects...";
if ($validator->findMissingObjects())
{
	echo "done (".count($validator->getMissingObjects())." found).";
}
else
{
	echo "nothing found.";
}

// STEP 2: Cleaning: Remove unbound references & tree entries
echo "<br/><br/>Cleaning...";

echo "<br/>Removing unbound references...";
if ($validator->removeUnboundReferences())
{
	echo "done.";
}
else
{
	echo "none. passed.";
}

echo "<br/>Removing unbound tree entries...";
if ($validator->removeUnboundChilds())
{
	echo "done.";
}
else
{
	echo "none. passed.";
}

// STEP 3: Restore objects
echo "<br/><br/>Restoring...";

echo "<br/>Restoring missing Objects...";
if ($validator->restoreMissingObjects())
{
	echo "done.";
}
else
{
	echo "none. passed.";
}

echo "<br/>Restoring unbounded childs & subtree entries...";
$validator->getChildsWithInvalidParent();
if ($validator->restoreUnboundChilds())
{
	echo "done.";
}
else
{
	echo "none. passed.";
}


// STEP $: Close gaps in tree
echo "<br/><br/>Cleaning...";
if ($validator->closeGapsInTree())
{
	echo "<br/>Closed gaps in tree...done";
}

// check RBAC starts here
// ...

// el fin
foreach ($validator->mode as $mode => $value)
{
	$arr[] = $mode."[".(int)$value."]";
}

$mode = implode(", ",$arr);

$tpl->setVariable("CONTENT", "<p>Tree ok. (Mode: ".$mode.")</p>");
$tpl->show()
?>
