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
//$validator->setMode("analyze",false);
//$validator->setMode("clean",true);
//$validator->setMode("purge_trash",true);

// STEP 1: Analyzing: Get all incomplete entries
echo "<br/><br/>Analyzing...";

if (!$validator->isModeEnabled("analyze"))
{
	echo "disabled.";
}
else
{
	echo "<br/>Searching for invalid references...";
	if ($validator->findInvalidReferences())
	{
		echo "done (".count($validator->getInvalidReferences())." found).";
	}
	else
	{
		echo "nothing found.";
	}
	
	echo "<br/>Searching for invalid tree entries...";
	if ($validator->findInvalidChilds())
	{
		echo "done (".count($validator->getInvalidChilds())." found).";
	}
	else
	{
		echo "nothing found.";
	}
	
	echo "<br/>Searching for missing objects...";
	if ($validator->findMissingObjects())
	{
		echo "done (".count($validator->getMissingObjects())." found).";
	}
	else
	{
		echo "nothing found.";
	}

	echo "<br/>Searching for unbound objects...";
	if ($validator->findUnboundObjects())
	{
		echo "done (".count($validator->getUnboundObjects())." found).";
	}
	else
	{
		echo "nothing found.";
	}

	echo "<br/>Searching for deleted objects...";
	if ($validator->findDeletedObjects())
	{
		echo "done (".count($validator->getDeletedObjects())." found).";
	}
	else
	{
		echo "nothing found.";
	}
}

// STEP 2: Cleaning: Remove unbound references & tree entries
echo "<br/><br/>Cleaning...";

if (!$validator->isModeEnabled("clean"))
{
	echo "disabled.";
}
else
{
	echo "<br/>Removing invalid references...";
	if ($validator->removeInvalidReferences())
	{
		echo "done.";
	}
	else
	{
		echo "nothing to remove. Skipped.";
	}
	
	echo "<br/>Removing invalid tree entries...";
	if ($validator->removeInvalidChilds())
	{
		echo "done.";
	}
	else
	{
		echo "nothing to remove. Skipped.";
	}
}

// find unbound objects again AFTER cleaning process!
$validator->findUnboundObjects();

// STEP 3: Restore objects
echo "<br/><br/>Restoring...";

if (!$validator->isModeEnabled("restore"))
{
	echo "disabled.";
}
else
{
	echo "<br/>Restoring missing Objects...";
	if ($validator->restoreMissingObjects())
	{
		echo "done.";
	}
	else
	{
		echo "nothing to restore. Skipped.";
	}
	
	echo "<br/>Restoring unbound objects & subobjects...";
	if ($validator->restoreUnboundObjects())
	{
		echo "done.";
	}
	else
	{
		echo "nothing to restore. Skipped.";
	}
}

// STEP 4: Restoring Trash
if ($validator->isModeEnabled("restore_trash"))
{
	echo "<br/><br/>Restoring trash...";
	if ($validator->restoreTrash())
	{
		echo "done";
	}
	else
	{
		echo "nothing to restore. Skipped.";
	}
}

// STEP 5: Purging...
if ($validator->isModeEnabled("purge"))
{
	echo "<br/><br/>Purging unbound objects...";
	if ($validator->purgeUnboundObjects())
	{
		echo "done";
	}
	else
	{
		echo "nothing to purge. Skipped.";
	}
}

// STEP 6: Purging...
if ($validator->isModeEnabled("purge_trash"))
{
	echo "<br/><br/>Purging trash...";
	if ($validator->purgeTrash())
	{
		echo "done";
	}
	else
	{
		echo "nothing to purge. Skipped.";
	}
}

// STEP 6: Close gaps in tree
if ($validator->isModeEnabled("clean"))
{
	echo "<br/><br/>Final cleaning...";
	if ($validator->closeGapsInTree())
	{
		echo "<br/>Closing gaps in tree...done";
	}
}

// check RBAC starts here
// ...

// el fin
foreach ($validator->mode as $mode => $value)
{
	$arr[] = $mode."[".(int)$value."]";
}

$mode = implode(", ",$arr);

$tpl->setVariable("CONTENT", "<p>Scan ok. <br/> (Mode: ".$mode.")</p>");
$tpl->show();

//$validator->findInvalidChilds();
//vd($validator->getUnboundObjects(),$validator->getMissingObjects(),$validator->getInvalidChilds(),$validator->getInvalidReferences(),$validator->getDeletedObjects());
?>
