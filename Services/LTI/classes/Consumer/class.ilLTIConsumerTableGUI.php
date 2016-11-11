<?php
/* Copyright (c) 1998-20016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
//require_once 'Services/LTI/classes/ActiveRecord/class.ilLTIExternalConsumer.php';

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
		$this->addColumn($lng->txt("lti_consumer_key"), "key");
		$this->addColumn($lng->txt("lti_consumer_secret"), "secret");
		$this->addColumn($lng->txt("in_use"), "language");
		$this->addColumn($lng->txt("objects"), "objects");
		$this->addColumn($lng->txt("role"), "role");
		$this->addColumn($lng->txt("active"), "active");
		$this->addColumn($lng->txt("actions"), "");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.lti_consumer_list_row.html", "Services/LTI");
		$this->setDefaultOrderField("title");

		$this->getItems();
	}

	/**
	 * Get consumer data
	 */
	function getItems()
	{
		$consumer_data = ilLTIExternalConsumer::getAll();
		$result = array();
		foreach ($consumer_data as $cons) {
			$result[] = array(
				"id" => $cons->getId(),
				"title" => $cons->gettitle(),
				"description" => $cons->getDescription(),
				"prefix" => $cons->getPrefix(),
				"key" => $cons->getKey(),
				"secret" => $cons->getSecret(),
				"language" => $cons->getLanguage(),
				"role" => $cons->getRole(),
				"active" => $cons->getActive()
			);
		}

		$this->setData($result);
	}

	/**
	 * Fill a single data row.
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		$this->tpl->setVariable("TXT_DESCRIPTION", $a_set["description"]);
		$this->tpl->setVariable("TXT_PREFIX", $a_set["prefix"]);
		$this->tpl->setVariable("TXT_KEY", $a_set["key"]);
		$this->tpl->setVariable("TXT_SECRET", $a_set["secret"]);
		$this->tpl->setVariable("TXT_LANGUAGE", $a_set["language"]);
		$obj_types = $this->parent_obj->object->getActiveObjectTypes($a_set["id"]);
		if($obj_types)
		{
			foreach($obj_types as $line)
			{
				$this->tpl->setCurrentBlock("objects");
				$this->tpl->setVariable("OBJECTS", $line);
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->tpl->setVariable("NO_OBJECTS", "-");
		}

		$this->tpl->setVariable("TXT_ROLE", $a_set["role"]);

		if($a_set["active"])
		{
			$this->tpl->setVariable("TXT_ACTIVE", $lng->txt('active'));
		}
		else
		{
			$this->tpl->setVariable("TXT_ACTIVE", $lng->txt('inactive'));
		}

	}
}