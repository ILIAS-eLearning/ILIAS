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



class ilPersonalDesktopGUI
{
	var $tpl;
	var $lng;
	var $ilias;



	function ilPersonalDesktopGUI()
	{
		global $ilias, $tpl, $lng, $rbacsystem;


		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ilias =& $ilias;
	}


	/**
	* display selected items
	*/
	function displaySelectedItems()
	{
		$types = array(
			array("title" => $this->lng->txt("learning_objects"),
			"types" => array("lm", "dbk", "slm", "htlm")),
			array("title" => $this->lng->txt("objs_glo"),
			"types" => "glo"),
			array("title" => $this->lng->txt("objs_tst"),
			"types" => "tst"),
			array("title" => $this->lng->txt("objs_frm"),
			"types" => "frm"),
			array("title" => $this->lng->txt("objs_chat"),
			"types" => "chat"),
			array("title" => $this->lng->txt("objs_mep"),
			"types" => "mep"),
			array("title" => $this->lng->txt("objs_grp"),
			"types" => "grp"),
			);
		$html = "";
		foreach($types as $type)
		{
			$html.= $this->getSelectedItemBlockHTML($type["title"], $type["types"]);
		}
		if ($html != "")
		{
			$this->tpl->setCurrentBlock("selected_items");
			$this->tpl->setVariable("TXT_SELECTED_ITEMS", $this->lng->txt("selected_items"));
			$this->tpl->setVariable("SELECTED_ITEMS", $html);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* get selected item block
	*/
	function getSelectedItemBlockHTML($a_title, $a_type)
	{
		$items = $this->ilias->account->getDesktopItems($a_type);

		if (count($items) > 0)
		{
			$tstCount = 0;
			$unsetCount = 0;
			$progressCount = 0;
			$unsetFlag = 0;
			$progressFlag = 0;
			$completedFlag = 0;
			if (strcmp($a_type, "tst") == 0) {
				$items = $this->multiarray_sort($items, "used_tries; title");
				foreach ($items as $tst_item) {
					if (!isset($tst_item["used_tries"])) {
						$unsetCount++;
					}
					elseif ($tst_item["used_tries"] == 0) {
						$progressCount++;
					}
				}
			}

			$tpl = new ilTemplate("tpl.usr_pd_selected_item_block.html", true, true);
			$tpl->setVariable("TXT_BLOCK_HEADER", $a_title);
			$img_type  = (is_array($a_type))
				? $a_type[0]
				: $a_type;

			$tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_".$img_type.".gif"));
			foreach($items as $item)
			{
				if (strcmp($a_type, "tst")==0) {
					$this->lng->loadLanguageModule("assessment");
					$tpl->setCurrentBlock("tbl_tstheader");
					if (($tstCount < $unsetCount)&&($unsetFlag==0)) {
						$tpl->setVariable("TXT_TST_TITLE", $this->lng->txt("tst_status_not_entered"));
						$unsetFlag++;
					}
					elseif (($tstCount < ($unsetCount+$progressCount))&&($progressFlag==0)) {
						$tpl->setVariable("TXT_TST_TITLE", $this->lng->txt("tst_status_progress"));
						$progressFlag++;
					}
					elseif (($tstCount >= ($unsetCount+$progressCount))&&($completedFlag==0)) {
						$tpl->setVariable("TXT_TST_TITLE", $this->lng->txt("tst_status_completed_more_tries_possible"));
						$completedFlag++;
					}
					$tstCount++;
					$tpl->parseCurrentBlock();
				}


				// edit link
				if ($item["edit_link"] != "")
				{
					$tpl->setCurrentBlock("edit_link");
					$tpl->setVariable("LINK_EDIT", $item["edit_link"]);
					$tpl->setVariable("TARGET_EDIT", "bottom");
					$tpl->setVariable("TXT_EDIT", "[".$this->lng->txt("edit")."]");
					$tpl->parseCurrentBlock();
				}
				else
				{
					$tpl->setVariable("EDIT", "&nbsp;");
				}

				// drop link
				$tpl->setCurrentBlock("drop_link");
				$tpl->setVariable("TYPE", $item["type"]);
				$tpl->setVariable("ID", $item["id"]);
				$tpl->setVariable("TXT_DROP", "[".$this->lng->txt("drop")."]");
				$tpl->parseCurrentBlock();

				// description
				if ($item["description"] != "")
				{
					$tpl->setCurrentBlock("description");
					$tpl->setVariable("TXT_ITEM_DESCRIPTION", $item["description"]);
					$tpl->parseCurrentBlock();
				}

				// show link
				$tpl->setCurrentBlock("block_row");
				$tpl->setVariable("LINK_SHOW", $item["link"]);
				$tpl->setVariable("TARGET_SHOW", $item["target"]);
				$tpl->setVariable("TXT_ITEM_TITLE", $item["title"]);
				$tpl->setVariable("ROWCOL","tblrow".(($i++ % 2)+1));
				$tpl->parseCurrentBlock();
			}
			return $tpl->get();
		}

		return "";
	}

	function displaySystemMessages()
	{
		// SYSTEM MAILS
		$umail = new ilMail($_SESSION["AccountId"]);
		$smails = $umail->getMailsOfFolder(0);

		if(count($smails))
		{
			// output mails
			$counter = 1;
			foreach ($smails as $mail)
			{
				// GET INBOX FOLDER FOR LINK_READ
				require_once "classes/class.ilMailbox.php";

				$mbox = new ilMailbox($_SESSION["AccountId"]);
				$inbox = $mbox->getInboxFolder();

				$this->tpl->setCurrentBlock("tbl_system_msg_row");
				$this->tpl->setVariable("ROWCOL",++$counter%2 ? 'tblrow1' : 'tblrow2');

				// GET SENDER NAME
				$user = new ilObjUser($mail["sender_id"]);

				if(!($fullname = $user->getFullname()))
				{
					$fullname = $this->lng->txt("unknown");
				}

				//new mail or read mail?
				$this->tpl->setVariable("MAILCLASS", $mail["status"] == 'read' ? 'mailread' : 'mailunread');
				$this->tpl->setVariable("MAIL_FROM", $fullname);
				$this->tpl->setVariable("MAIL_SUBJ", $mail["m_subject"]);
				$this->tpl->setVariable("MAIL_DATE", ilFormat::formatDate($mail["send_time"]));
				$target_name = htmlentities(urlencode("mail_read.php?mobj_id=".$inbox."&mail_id=".$mail["mail_id"]));
				$this->tpl->setVariable("MAIL_LINK_READ", "mail_frameset.php?target=".$target_name);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("tbl_system_msg");
			//headline
			$this->tpl->setVariable("SYSTEM_MAILS",$this->lng->txt("mail_system"));
			//columns headlines
			$this->tpl->setVariable("TXT_SENDER", $this->lng->txt("sender"));
			$this->tpl->setVariable("TXT_SUBJECT", $this->lng->txt("subject"));
			$this->tpl->setVariable("TXT_DATETIME",$this->lng->txt("date")."/".$this->lng->txt("time"));
			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	* display users online
	*/
	function displayUsersOnline()
	{
		$this->tpl->setVariable("TXT_USERS_ONLINE",$this->lng->txt("users_online"));

		$users = ilUtil::getUsersOnline();

		$num = 0;

		foreach ($users as $user_id => $user)
		{
			if ($user_id != ANONYMOUS_USER_ID)
			{
				$num++;
			}
			else
			{
				$guests = $user["num"];
			}
		}

		// parse guests text
		if (empty($guests))
		{
			$guest_text = "";
		}
		elseif ($guests == "1")
		{
			$guest_text = "1 ".$this->lng->txt("guest");
		}
		else
		{
			$guest_text = $guests." ".$this->lng->txt("guests");
		}

		// parse registered users text
		if ($num > 0)
		{
			if ($num == 1)
			{
				$user_list = $num." ".$this->lng->txt("registered_user");
			}
			else
			{
				$user_list = $num." ".$this->lng->txt("registered_users");
			}

			// add details link
			if ($_GET["cmd"] == "whoisdetail")
			{
				$text = $this->lng->txt("hide_details");
				$cmd = "hidedetails";
			}
			else
			{
				$text = $this->lng->txt("show_details");
				$cmd = "whoisdetail";
			}

			$user_details_link = "&nbsp;&nbsp;<span style=\"font-weight:lighter\">[</span><a class=\"std\" href=\"usr_personaldesktop.php?cmd=".$cmd."\">".$text."</a><span style=\"font-weight:lighter\">]</span>";

			if (!empty($guest_text))
			{
				$user_list .= " ".$this->lng->txt("and")." ".$guest_text;
			}

			$user_list .= $user_details_link;
		}
		else
		{
			$user_list = $guest_text;
		}

		$this->tpl->setVariable("USER_LIST",$user_list);

		// display details of users online
		if ($_GET["cmd"] == "whoisdetail")
		{
			$z = 0;

			foreach ($users as $user_id => $user)
			{
				if ($user_id != ANONYMOUS_USER_ID)
				{
					$rowCol = ilUtil::switchColor($z,"tblrow2","tblrow1");
					$login_time = ilFormat::dateDiff(ilFormat::datetime2unixTS($user["last_login"]),time());

					// hide mail-to icon for anonymous users
					if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID and $_SESSION["AccountId"] != $user_id)
					{
						$this->tpl->setCurrentBlock("mailto_link");
						$this->tpl->setVariable("IMG_MAIL", ilUtil::getImagePath("icon_pencil_b.gif", false));
						$this->tpl->setVariable("ALT_TXT_MAIL",$this->lng->txt("mail"));
						$this->tpl->setVariable("USR_LOGIN",$user["login"]);
						$this->tpl->parseCurrentBlock();
					}

					// check for profile
					// todo: use user class!
					$q = "SELECT value FROM usr_pref WHERE usr_id='".$user_id."' AND keyword='public_profile' AND value='y'";
					$r = $this->ilias->db->query($q);

					if ($r->numRows())
					{
						$this->tpl->setCurrentBlock("profile_link");
						$this->tpl->setVariable("IMG_VIEW", ilUtil::getImagePath("enlarge.gif", false));
						$this->tpl->setVariable("ALT_TXT_VIEW",$this->lng->txt("view"));
						$this->tpl->setVariable("USR_ID",$user_id);
						$this->tpl->parseCurrentBlock();
					}

					$this->tpl->setCurrentBlock("tbl_users_row");
					$this->tpl->setVariable("ROWCOL",$rowCol);
					$this->tpl->setVariable("USR_LOGIN",$user["login"]);
					$this->tpl->setVariable("USR_FULLNAME",ilObjUser::setFullname($user["title"],$user["firstname"],$user["lastname"]));
					$this->tpl->setVariable("USR_LOGIN_TIME",$login_time);

					$this->tpl->parseCurrentBlock();

					$z++;
				}
			}

			if ($z > 0)
			{
				$this->tpl->setCurrentBlock("tbl_users_header");
				$this->tpl->setVariable("TXT_USR_LOGIN",ucfirst($this->lng->txt("username")));
				$this->tpl->setVariable("TXT_USR_FULLNAME",ucfirst($this->lng->txt("fullname")));
				$this->tpl->setVariable("TXT_USR_LOGIN_TIME",ucfirst($this->lng->txt("login_time")));
				$this->tpl->parseCurrentBlock();
			}
		}
	}


	/**
	* display bookmarks
	*/
	function displayBookmarks()
	{
		include_once("classes/class.ilBookmarkFolder.php");
		if (!empty($_GET["curBMFolder"]))
		{
			$_SESSION["ilCurBMFolder"] = $_GET["curBMFolder"];
		}
		$bm_items = ilBookmarkFolder::getObjects($_SESSION["ilCurBMFolder"]);
		$i = 0;

		if (!ilBookmarkFolder::isRootFolder($_SESSION["ilCurBMFolder"])
			&& !empty($_SESSION["ilCurBMFolder"]))
		{
			$i++;
			$this->tpl->setCurrentBlock("tbl_bm_row");
			$this->tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
			$this->tpl->setVariable("BM_TITLE", "<img border=\"0\" vspace=\"0\" align=\"left\" src=\"".
				ilUtil::getImagePath("icon_cat.gif")."\">&nbsp;"."..");
			$this->tpl->setVariable("BM_LINK", "usr_personaldesktop.php?curBMFolder=".
				ilBookmarkFolder::getRootFolder());
			$this->tpl->parseCurrentBlock();
		}

		foreach ($bm_items as $bm_item)
		{
			$i++;
			$this->tpl->setCurrentBlock("tbl_bm_row");
			$this->tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
			$this->tpl->setVariable("BM_LINK", "target URL");

			switch ($bm_item["type"])
			{
				case "bmf":
					$this->tpl->setVariable("BM_TITLE", "<img border=\"0\" vspace=\"0\" align=\"left\" src=\"".
						ilUtil::getImagePath("icon_cat.gif")."\">&nbsp;".$bm_item["title"]);
					$this->tpl->setVariable("BM_LINK", "usr_personaldesktop.php?curBMFolder=".$bm_item["obj_id"]);
					break;

				case "bm":
					$this->tpl->setVariable("BM_TITLE", "<img border=\"0\" vspace=\"0\" align=\"left\" src=\"".
						ilUtil::getImagePath("icon_bm.gif")."\">&nbsp;".$bm_item["title"]);
					$this->tpl->setVariable("BM_LINK", $bm_item["target"]);
					break;
			}

			$this->tpl->parseCurrentBlock();
		}

		if ($i == 0)
		{
			$this->tpl->setCurrentBlock("tbl_no_bm");
			$this->tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
			$this->tpl->setVariable("TXT_NO_BM", $this->lng->txt("no_bm_in_personal_list"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("tbl_bm");
		$this->tpl->setVariable("TXT_BM_HEADER",$this->lng->txt("my_bms"));
		$this->tpl->setVariable("TXT_BM_TITLE",$this->lng->txt("title"));
		$this->tpl->parseCurrentBlock();
	}

/**
* Returns the multidimenstional sorted array
*
* Returns the multidimenstional sorted array
*
* @author		Muzaffar Altaf <maltaf@tzi.de>
* @param array $arrays The array to be sorted
* @param string $key_sort The keys on which array must be sorted
* @access public
*/
	function multiarray_sort ($array, $key_sort)
	{
		if ($array) {
			$key_sorta = explode(";", $key_sort);

			$multikeys = array_keys($array);
			$keys = array_keys($array[$multikeys[0]]);

			for($m=0; $m < count($key_sorta); $m++) {
				$nkeys[$m] = trim($key_sorta[$m]);
			}
			$n += count($key_sorta);

			for($i=0; $i < count($keys); $i++){
				if(!in_array($keys[$i], $key_sorta)) {
					$nkeys[$n] = $keys[$i];
					$n += "1";
				}
			}

			for($u=0;$u<count($array); $u++) {
				$arr = $array[$multikeys[$u]];
				for($s=0; $s<count($nkeys); $s++) {
					$k = $nkeys[$s];
					$output[$multikeys[$u]][$k] = $array[$multikeys[$u]][$k];
				}
			}
			sort($output);
			return $output;
		}
	}
}
?>
