<?php
/* Copyright (c) 1998-20016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for LTI consumer listing
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesLTI
 */
class ilObjectConsumerTableGUI extends ilTable2GUI
{
	function __construct($a_parent_obj, $a_parent_cmd, $a_template_context)
	{
		global $ilCtrl, $lng;

		$this->setId("ltioconsumer");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setLimit(9999);

		$this->setTitle($lng->txt("lti_object_consumer"));

		$this->addColumn($lng->txt("title"), "title");
		$this->addColumn($lng->txt("description"), "description");
		$this->addColumn($lng->txt("prefix"), "prefix");
		$this->addColumn($lng->txt("consumer_key"), "key");
		$this->addColumn($lng->txt("in_use"), "language");
		$this->addColumn($lng->txt("objects"), "objects");
		$this->addColumn($lng->txt("active"), "active");
		$this->addColumn($lng->txt("actions"), "");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		//$this->setRowTemplate("tpl.object_consumer_row.html", "Services/LTI");
		$this->setDefaultOrderField("title");

		//$this->getItems();
	}
}