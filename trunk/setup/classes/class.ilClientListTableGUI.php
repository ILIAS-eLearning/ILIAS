<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Client list table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSetup
 */
class ilClientListTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_setup)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct(null, "");
		$this->setTitle($lng->txt("clients"));
		$this->setLimit(9999);
		$this->setup = $a_setup;

		$this->addColumn($this->lng->txt(""), "", "1");
		$this->addColumn($this->lng->txt("name"), "");
		$this->addColumn($this->lng->txt("id"), "");
		$this->addColumn($this->lng->txt("login"), "");
		$this->addColumn($this->lng->txt("details"), "");
		$this->addColumn($this->lng->txt("status"), "");
		$this->addColumn($this->lng->txt("access"), "");
		
		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");
		
		$this->setEnableHeader(true);
		$this->setFormAction("setup.php?cmd=gateway");
		$this->setRowTemplate("tpl.client_list_row.html", "setup");
		$this->disable("footer");
		$this->setEnableTitle(true);

		$this->getClients();
		
		$this->addMultiCommand("changedefault", $lng->txt("set_default_client"));
	}

	/**
	 * Get clients
	 *
	 * @param
	 * @return
	 */
	function getClients()
	{
		global $lng;
		
		$clients = array();
		$clientlist = new ilClientList($this->setup->db_connections);
		$list = $clientlist->getClients();

		foreach ($list as $key => $client)
		{
			// check status 
			$status_arr = $this->setup->getStatus($client);
			if (!$status_arr["db"]["status"])
			{
				$status =
					"<a href=\"setup.php?cmd=db&client_id=".$key."&back=clientlist\">". 
					$status_arr["db"]["comment"]."</a>";
			}
			elseif (!$status_arr["finish"]["status"])
			{
				$status = $lng->txt("setup_not_finished");
			}
			else
			{
				$status = "<font color=\"green\"><strong>OK</strong></font>";
			}
			
			if ($status_arr["access"]["status"])
			{
				$access = "online";
			}
			else
			{
				$access = "disabled";
			}
			
			if ($key == $this->setup->default_client)
			{
				$default = " checked=\"checked\"";
			}
			else
			{
				$default = "";
			}
			
			if ($status_arr["finish"]["status"] and $status_arr["access"]["status"])
			{
				$login = "<a href=\"../login.php?client_id=".$key."\">Login</a>";
			}
			else
			{
				$login = "&nbsp;";
			}

			$access_html = "<a href=\"setup.php?cmd=changeaccess&client_id=".$key."&back=clientlist\">".$this->lng->txt($access)."</a>";
			
			$client_name = ($client->getName()) ? $client->getName() : "&lt;".$lng->txt("no_client_name")."&gt;";
			
			// visible data part
			$clients[] = array(
				"default"       => "<input type=\"radio\" name=\"form[default]\" value=\"".$key."\"".$default."/>",
				"name"          => $client_name,
				"desc"          => $client->getDescription(),
				"id"            => $key,
				"login"         => $login,
				"details"       => "<a href=\"setup.php?cmd=view&client_id=".$key."\">Details</a>",
				"status"        => $status,
				"access_html"   => $access_html
				);
		}
	
		$this->setData($clients);
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("DEF_RADIO", $a_set["default"]);
		$this->tpl->setVariable("NAME", $a_set["name"]);
		$this->tpl->setVariable("DESC", $a_set["desc"]);
		$this->tpl->setVariable("ID", $a_set["id"]);
		$this->tpl->setVariable("LOGIN", $a_set["login"]);
		$this->tpl->setVariable("DETAILS", $a_set["details"]);
		$this->tpl->setVariable("STATUS", $a_set["status"]);
		$this->tpl->setVariable("ACCESS", $a_set["access_html"]);
	}

}
?>
