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
		global $ilias, $tpl, $lng;

		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ilias =& $ilias;
	}

	/**
	* show learning resources
	*/
	function displayLearningResources()
	{
		global $rbacsystem;

		$i = 0;
		$types = array("lm", "dbk", "glo");

		foreach ($types as $type)
		{
			$lo_items = $this->ilias->account->getDesktopItems($type);

			foreach ($lo_items as $lo_item)
			{
				$i++;
				$this->tpl->setCurrentBlock("tbl_lo_row");
				$this->tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
				$this->tpl->setVariable("LO_LINK", $lo_item["link"]);
				$this->tpl->setVariable("LO_TARGET", $lo_item["target"]);
				$img = "<img border=\"0\" vspace=\"0\" align=\"left\" src=\"".
					ilUtil::getImagePath("icon_".$lo_item["type"].".gif")."\">&nbsp;";
				$this->tpl->setVariable("LO_TITLE", $img.$lo_item["title"]);
				$this->tpl->setVariable("DROP_LINK", "usr_personaldesktop.php?cmd=dropItem&type=".$type."&id=".$lo_item["id"]);
				$this->tpl->setVariable("TXT_DROP", "(".$this->lng->txt("drop").")");
				if ($rbacsystem->checkAccess('write', $lo_item["ref_id"]))
				{
					$this->tpl->setVariable("EDIT_LINK", $lo_item["edit_link"]);
					$this->tpl->setVariable("TXT_EDIT", "(".$this->lng->txt("edit").")");
				}
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($i == 0)
		{
			$this->tpl->setCurrentBlock("tbl_no_lo");
			$this->tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
			$this->tpl->setVariable("TXT_NO_LO", $this->lng->txt("no_lo_in_personal_list"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("tbl_lo");
		$this->tpl->setVariable("TXT_LO_HEADER",$this->lng->txt("my_los"));
		$this->tpl->setVariable("TXT_LO_TITLE",$this->lng->txt("title"));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* display forums
	*/
	function displayForums()
	{
		//********************************************
		// forums
		$frm_items = $this->ilias->account->getDesktopItems("frm");
		$i = 0;

		foreach ($frm_items as $frm_item)
		{
			$i++;
			$this->tpl->setCurrentBlock("tbl_frm_row");
			$this->tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
			$this->tpl->setVariable("FRM_LINK", "forums_threads_liste.php?ref_id=".$frm_item["id"]."&backurl=forums");
			$this->tpl->setVariable("FRM_TITLE", $frm_item["title"]);
			$this->tpl->setVariable("DROP_LINK", "usr_personaldesktop.php?cmd=dropItem&type=frm&id=".$frm_item["id"]);
			$this->tpl->setVariable("TXT_DROP", "(".$this->lng->txt("drop").")");
			$this->tpl->parseCurrentBlock();
		}

		if ($i == 0)
		{
			$this->tpl->setCurrentBlock("tbl_no_frm");
			$this->tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
			$this->tpl->setVariable("TXT_NO_FRM", $this->lng->txt("no_frm_in_personal_list"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("tbl_frm");
		$this->tpl->setVariable("TXT_FRM_HEADER",$this->lng->txt("my_frms"));
		$this->tpl->setVariable("TXT_FRM_TITLE",$this->lng->txt("title"));
		$this->tpl->parseCurrentBlock();

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

			$user_details_link = "<a class=\"std\" href=\"usr_personaldesktop.php?cmd=".$cmd."\"> [".$text."]</a>";

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
	* display groups
	*/
	function displayGroups()
	{
		$grp_items = $this->ilias->account->getDesktopItems("grp");
		$i = 0;

		foreach ($grp_items as $grp_item)
		{
			$i++;
			$this->tpl->setCurrentBlock("tbl_grp_row");
			$this->tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
			$this->tpl->setVariable("GRP_LINK", "group.php?ref_id=".$grp_item["id"]);
			$this->tpl->setVariable("GRP_TITLE", $grp_item["title"]);
			$this->tpl->setVariable("DROP_LINK", "usr_personaldesktop.php?cmd=leaveGroup&id=".$grp_item["id"]);
			$this->tpl->setVariable("TXT_DROP", "(".$this->lng->txt("unsubscribe").")");
			$this->tpl->parseCurrentBlock();
		}

		if ($i == 0)
		{
			$this->tpl->setCurrentBlock("tbl_no_grp");
			$this->tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
			$this->tpl->setVariable("TXT_NO_GRP", $this->lng->txt("no_grp_in_personal_list"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("tbl_grp");
		$this->tpl->setVariable("TXT_GRP_HEADER",$this->lng->txt("my_grps"));
		$this->tpl->setVariable("TXT_GRP_TITLE",$this->lng->txt("title"));
		$this->tpl->parseCurrentBlock();
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
}
?>
