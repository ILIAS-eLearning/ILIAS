<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for wiki page templates
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesWIki
 */
class ilWikiPageTemplatesTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_wiki_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		include_once("./Modules/Wiki/classes/class.ilWikiPageTemplate.php");
		$templates = new ilWikiPageTemplate($a_wiki_id);
		$this->setData($templates->getAllInfo());
		$this->setTitle($lng->txt(""));

		$this->addColumn($this->lng->txt(""), "", "1");
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("wiki_templ_new_pages"), "");
		$this->addColumn($this->lng->txt("wiki_templ_add_to_page"), "");

		$this->setDefaultOrderDirection("asc");
		$this->setDefaultOrderField("title");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.wiki_page_templates_row.html", "Modules/Wiki");

		$this->addMultiCommand("remove", $lng->txt("wiki_remove_template_status"));
		$this->addCommandButton("saveTemplateSettings", $lng->txt("save"));
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		$this->tpl->setVariable("ID", $a_set["wpage_id"]);
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		if ($a_set["new_pages"])
		{
			$this->tpl->setVariable("NEW_PAGES_CHECKED", 'checked="checked"');
		}
		if ($a_set["add_to_page"])
		{
			$this->tpl->setVariable("ADD_TO_PAGE_CHECKED", 'checked="checked"');
		}
	}

}
?>