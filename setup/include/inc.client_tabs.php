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
* buttons for client details
* 
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*
* @package	ilias-setup
*/

$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

$tab = $this->cmd ? $this->cmd : "view";

//if (!isset($_SESSION["ClientId"]))
//{
	$client_id = "client_id=".$_GET["client_id"];
//}

// overview
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE",$tab == "view" ? "tabactive" : "tabinactive");
$this->tpl->setVariable("TAB_LINK","setup.php?".$client_id."&cmd=view");
$this->tpl->setVariable("TAB_TEXT",ucfirst($this->lng->txt("overview")));
$this->tpl->parseCurrentBlock();

// database
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE",$tab == "db" ? "tabactive" : "tabinactive");
$this->tpl->setVariable("TAB_LINK","setup.php?".$client_id."&cmd=db");
$this->tpl->setVariable("TAB_TEXT",ucfirst($this->lng->txt("database")));
$this->tpl->parseCurrentBlock();

// languages
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE",$tab == "lang" ? "tabactive" : "tabinactive");
$this->tpl->setVariable("TAB_LINK","setup.php?cmd=lang");
$this->tpl->setVariable("TAB_TEXT",ucfirst($this->lng->txt("languages")));
$this->tpl->parseCurrentBlock();
// contact data
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE",$tab == "contact" ? "tabactive" : "tabinactive");
$this->tpl->setVariable("TAB_LINK","setup.php?cmd=contact");
$this->tpl->setVariable("TAB_TEXT",ucfirst($this->lng->txt("contact")));
$this->tpl->parseCurrentBlock();

// ilias-NIC
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE",$tab == "nic" ? "tabactive" : "tabinactive");
$this->tpl->setVariable("TAB_LINK","setup.php?cmd=nic");
$this->tpl->setVariable("TAB_TEXT",ucfirst($this->lng->txt("ilias_nic")));
$this->tpl->parseCurrentBlock();

// setup settings
/* disabled
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE",$tab == "settings" ? "tabactive" : "tabinactive");
$this->tpl->setVariable("TAB_LINK","setup.php?cmd=settings&lang=".$this->lang);
$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("settings"));
$this->tpl->parseCurrentBlock();*/

// delete confirmation
if ((is_object($this->setup) && $this->setup->isAdmin()) || $this->isAdmin())
{
	$this->tpl->setCurrentBlock("tab");
	$this->tpl->setVariable("TAB_TYPE",$tab == "delete" ? "tabactive" : "tabinactive");
	$this->tpl->setVariable("TAB_LINK","setup.php?cmd=delete&lang=".$this->lang);
	$this->tpl->setVariable("TAB_TEXT",ucfirst($this->lng->txt("delete")));
	$this->tpl->parseCurrentBlock();
}

// ilias-NIC
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE",$tab == "tools" ? "tabactive" : "tabinactive");
$this->tpl->setVariable("TAB_LINK","setup.php?cmd=tools");
$this->tpl->setVariable("TAB_TEXT",ucfirst($this->lng->txt("tools")));
$this->tpl->parseCurrentBlock();

?>
