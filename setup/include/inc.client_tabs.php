<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* buttons for client details
*
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*
* @package	ilias-setup
*/

$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html", "Services/UIComponent/Tabs");

$tab = $this->cmd ? $this->cmd : "view";

//if (!isset($_SESSION["ClientId"]))
//{
    $client_id = "client_id=" . $_GET["client_id"];
//}

// overview
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE", $tab == "view" ? "active" : "");
$this->tpl->setVariable("TAB_LINK", "setup.php?" . $client_id . "&cmd=view");
$this->tpl->setVariable("TAB_TEXT", ucfirst($this->lng->txt("overview")));
$this->tpl->parseCurrentBlock();

// database
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE", in_array($tab, array("db", "displayDatabase", "showUpdateSteps", "dbslave")) ? "active" : "");
$this->tpl->setVariable("TAB_LINK", "setup.php?" . $client_id . "&cmd=db");
$this->tpl->setVariable("TAB_TEXT", ucfirst($this->lng->txt("database")));
$this->tpl->parseCurrentBlock();

// sessions
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE", $tab == "sess" ? "active" : "");
$this->tpl->setVariable("TAB_LINK", "setup.php?" . $client_id . "&cmd=sess");
$this->tpl->setVariable("TAB_TEXT", ucfirst($this->lng->txt("session_management")));
$this->tpl->parseCurrentBlock();

// languages
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE", $tab == "lang" ? "active" : "");
$this->tpl->setVariable("TAB_LINK", "setup.php?cmd=lang");
$this->tpl->setVariable("TAB_TEXT", ucfirst($this->lng->txt("languages")));
$this->tpl->parseCurrentBlock();
// contact data
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE", $tab == "contact" ? "active" : "");
$this->tpl->setVariable("TAB_LINK", "setup.php?cmd=contact");
$this->tpl->setVariable("TAB_TEXT", ucfirst($this->lng->txt("contact")));
$this->tpl->parseCurrentBlock();

// proxy tab
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE", in_array($tab, array("proxy", "displayProxy", "saveProxy")) ? "active" : "");
$this->tpl->setVariable("TAB_LINK", "setup.php?cmd=proxy");
$this->tpl->setVariable("TAB_TEXT", ucfirst($this->lng->txt("proxy")));
$this->tpl->parseCurrentBlock();

// global cache
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE", in_array($tab, array("cache", "displayCache", "saveCache")) ? "active" : "");
$this->tpl->setVariable("TAB_LINK", "setup.php?cmd=cache");
$this->tpl->setVariable("TAB_TEXT", ucfirst($this->lng->txt("global_cache")));
$this->tpl->parseCurrentBlock();

// ilias-NIC
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE", $tab == "nic" ? "active" : "");
$this->tpl->setVariable("TAB_LINK", "setup.php?cmd=nic");
$this->tpl->setVariable("TAB_TEXT", ucfirst($this->lng->txt("ilias_nic")));
$this->tpl->parseCurrentBlock();

// Populate
if ($this->setup->isAdmin()) {
    $this->tpl->setCurrentBlock("tab");
    $this->tpl->setVariable("TAB_TYPE", $tab == "cloneSelectSource" ? "active" : "");
    $this->tpl->setVariable("TAB_LINK", "setup.php?cmd=cloneSelectSource");
    $this->tpl->setVariable("TAB_TEXT", ucfirst($this->lng->txt("populate")));
    $this->tpl->parseCurrentBlock();
}

// setup settings
/* disabled
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE",$tab == "settings" ? "active" : "");
$this->tpl->setVariable("TAB_LINK","setup.php?cmd=settings&lang=".$this->lang);
$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("settings"));
$this->tpl->parseCurrentBlock();*/

// delete confirmation
if ((is_object($this->setup) && $this->setup->isAdmin())) {
    $this->tpl->setCurrentBlock("tab");
    $this->tpl->setVariable("TAB_TYPE", $tab == "delete" ? "active" : "");
    $this->tpl->setVariable("TAB_LINK", "setup.php?cmd=delete&lang=" . $this->lang);
    $this->tpl->setVariable("TAB_TEXT", ucfirst($this->lng->txt("delete")));
    $this->tpl->parseCurrentBlock();
}

// ilias-NIC
$this->tpl->setCurrentBlock("tab");
$this->tpl->setVariable("TAB_TYPE", ($tab == "tools" || $tab == "reloadStructure" || $tab == "switchTree" || $tab == "changeSettingsType" || $tab == "showLongerSettings") ? "active" : "");
$this->tpl->setVariable("TAB_LINK", "setup.php?cmd=tools");
$this->tpl->setVariable("TAB_TEXT", ucfirst($this->lng->txt("tools")));
$this->tpl->parseCurrentBlock();
