<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once "Services/Mail/classes/class.ilMailbox.php";
require_once "Services/Mail/classes/class.ilFormatMail.php";

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
*/
class ilMailOptionsGUI
{
	private $tpl = null;
	private $ctrl = null;
	private $lng = null;
	
	private $umail = null;
	private $mbox = null;

	private $errorDelete = false;

	public function __construct()
	{
		global $tpl, $ilCtrl, $lng, $ilUser;

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		
		$this->ctrl->saveParameter($this, "mobj_id");

		$this->umail = new ilFormatMail($ilUser->getId());
		$this->mbox = new ilMailBox($ilUser->getId());
	}

	public function executeCommand()
	{
		$forward_class = $this->ctrl->getNextClass($this);
		switch($forward_class)
		{
			default:
				if (!($cmd = $this->ctrl->getCmd()))
				{
					$cmd = "showOptions";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	public function saveOptions()
	{
		$this->umail->mail_options->updateOptions(ilUtil::stripSlashes($_POST["signature"]), ilUtil::stripSlashes((int) $_POST["linebreak"]), ilUtil::stripSlashes((int) $_POST["incoming_type"]), ilUtil::stripSlashes((int) $_POST['cronjob_notification']));
#		ilUtil::sendInfo($this->lng->txt("mail_options_saved"),true);
#		$this->ctrl->redirectByClass("ilmailfoldergui");
		ilUtil::sendInfo($this->lng->txt("mail_options_saved"));
		$this->showOptions();
		
	}

	public function showOptions()
	{
		global $ilUser, $ilias;
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_options.html", "Services/Mail");
		$this->tpl->setVariable("HEADER", $this->lng->txt("mail"));

		// FORM GLOBAL OPTIONS
		
		$this->tpl->setCurrentBlock("options");
	
		// BEGIN INCOMING
		$this->tpl->setCurrentBlock("option_inc_line");

		$inc = array($this->lng->txt("mail_incoming_local"),$this->lng->txt("mail_incoming_smtp"),$this->lng->txt("mail_incoming_both"));
		foreach($inc as $key => $option)
		{
			$this->tpl->setVariable("OPTION_INC_VALUE",$key);
			$this->tpl->setVariable("OPTION_INC_NAME",$option);
			$this->tpl->setVariable("OPTION_INC_SELECTED",$this->umail->mail_options->getIncomingType() == $key ? "selected=\"selected\"" : "");
			$this->tpl->parseCurrentBlock();
		}
	
		// BEGIN LINEBREAK_OPTIONS
		$this->tpl->setCurrentBlock("option_line");
		$linebreak = $this->umail->mail_options->getLinebreak();
		
		for($i = 50; $i <= 80;$i++)
		{
			$this->tpl->setVariable("OPTION_VALUE",$i);
			$this->tpl->setVariable("OPTION_NAME",$i);
			if( $i == $linebreak)
			{
				$this->tpl->setVariable("OPTION_SELECTED","selected");
			}
			$this->tpl->parseCurrentBlock();
		}

		if(!strlen(ilObjUser::_lookupEmail($ilUser->getId())))
		{
			$this->tpl->setVariable('INC_DISABLED','disabled="disabled"');
		}			
		
		$this->tpl->setVariable("GLOBAL_OPTIONS",$this->lng->txt("mail_global_options"));
		$this->tpl->setVariable("TXT_INCOMING", $this->lng->txt("mail_incoming"));
		$this->tpl->setVariable("TXT_LINEBREAK", $this->lng->txt("linebreak"));
		$this->tpl->setVariable("TXT_SIGNATURE", $this->lng->txt("signature"));
		$this->tpl->setVariable("CONTENT",$this->umail->mail_options->getSignature());
		
		if ($ilias->getSetting("mail_notification"))
		{
			$this->tpl->setVariable("TXT_CRONJOB_NOTIFICATION", $this->lng->txt("cron_mail_notification"));
			$this->tpl->setVariable("TXT_CRONJOB_NOTIFICATION_INFO", $this->lng->txt("mail_cronjob_notification_info"));
			if ($this->umail->mail_options->getCronjobNotification())
			{
				$this->tpl->setVariable("CRONJOB_NOTIFICATION_SELECTED", " checked=\"checked\"");
			}
		}
		
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		$this->ctrl->setParameter($this, "cmd", "post");
		$this->tpl->setVariable("ACTION", $this->ctrl->getFormAction($this, 'saveOptions'));
		$this->ctrl->clearParameters($this);

		$this->tpl->parseCurrentBlock();
		
		$this->tpl->show();
	}

}

?>