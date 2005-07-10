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
			
		$html = "";
		
		$html.= $this->getSelectedItemsBlockHTML();
		
		if ($html != "")
		{
			$this->tpl->setCurrentBlock("selected_items");
			$this->tpl->setVariable("SELECTED_ITEMS", $html);
			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	 * get selected item block
	 */
	function getSelectedItemsBlockHTML()
	{
		include_once './classes/class.ilRepositoryExplorer.php';

		global $rbacsystem, $objDefinition, $ilBench;
		
		$output = false;
		$types = array(
			array("title" => $this->lng->txt("objs_cat"), "types" => "cat"),
			array("title" => $this->lng->txt("objs_fold"), "types" => "fold"),
			array("title" => $this->lng->txt("objs_crs"), "types" => "crs"),
			array("title" => $this->lng->txt("objs_grp"), "types" => "grp"),
			array("title" => $this->lng->txt("objs_chat"), "types" => "chat"),
			array("title" => $this->lng->txt("objs_frm"), "types" => "frm"),
			array("title" => $this->lng->txt("learning_resources"),"types" => array("lm", "htlm", "sahs", "dbk")),
			array("title" => $this->lng->txt("objs_glo"), "types" => "glo"),
			array("title" => $this->lng->txt("objs_file"), "types" => "file"),
			array("title" => $this->lng->txt("objs_webr"), "types" => "webr"),
			array("title" => $this->lng->txt("objs_exc"), "types" => "exc"),
			array("title" => $this->lng->txt("objs_tst"), "types" => "tst"),
			array("title" => $this->lng->txt("objs_svy"), "types" => "svy"),
			array("title" => $this->lng->txt("objs_mep"), "types" => "mep"),
			array("title" => $this->lng->txt("objs_qpl"), "types" => "qpl"),
			array("title" => $this->lng->txt("objs_spl"), "types" => "spl"),
			array("title" => $this->lng->txt("objs_icrs"), "types" => "icrs"),
			array("title" => $this->lng->txt("objs_icla"), "types" => "icla")
		);
		
		//$html = "";
		
		$tpl =& $this->newBlockTemplate();
		
		foreach ($types as $type)
		{
			$type = $type["types"];
			$title = $type["title"];
			
			$items = $this->ilias->account->getDesktopItems($type);
			$item_html = array();

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
			
				/*
				$tpl = new ilTemplate("tpl.usr_pd_selected_item_block.html", true, true);
				$tpl->setVariable("TXT_BLOCK_HEADER", $a_title);
				$img_type  = (is_array($a_type))
					? $a_type[0]
					: $a_type;*/
	
				//$tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_".$img_type.".gif"));
				
				//$this->lng->loadLanguageModule("assessment");
				//$this->lng->loadLanguageModule("survey");
				//$this->lng->loadLanguageModule("crs");
				foreach($items as $item)
				{
					// special test handling
					/*
					if (strcmp($a_type, "tst")==0) {
						
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
					}*/
					
					// get list gui class for each object type
					if ($cur_obj_type != $item["type"])
					{
						$class = $objDefinition->getClassName($item["type"]);
						$location = $objDefinition->getLocation($item["type"]);
						$full_class = "ilObj".$class."ListGUI";
						include_once($location."/class.".$full_class.".php");
						$item_list_gui = new $full_class();
						$item_list_gui->enableDelete(false);
						$item_list_gui->enableCut(false);
						$item_list_gui->enablePayment(false);
						$item_list_gui->enableLink(false);
					}
					// render item row
					$ilBench->start("ilPersonalDesktopGUI", "getListHTML");
	
					$html = $item_list_gui->getListItemHTML($item["ref_id"],
						$item["obj_id"], $item["title"], $item["description"]);
					$ilBench->stop("ilPersonalDesktopGUI", "getListHTML");
					if ($html != "")
					{
						$item_html[] = array("html" => $html, "item_id" => $item["ref_id"]);

						/*
						$tpl->setVariable("ITEM_HTML", $html);
						$tpl->setCurrentBlock("block_row");
						$tpl->setVariable("ROWCOL","tblrow".(($i++ % 2)+1));
						$tpl->parseCurrentBlock();
						*/
					}
				}

				// output block for resource type
				if (count($item_html) > 0)
				{
					// add a header for each resource type
					$this->addHeaderRow($tpl, $type);
					$this->resetRowType();

					// content row
					foreach($item_html as $item)
					{
						$this->addStandardRow($tpl, $item["html"], $item["item_id"]);
						$output = true;
					}
				}
			}
		}
		
		if ($output)
		{
			$tpl->setCurrentBlock("pd_header_row");
			$tpl->setVariable("PD_BLOCK_HEADER_CONTENT", $this->lng->txt("selected_items"));
			$tpl->parseCurrentBlock();
		}
		
		return $tpl->get();
    }
	
	/**
	* adds a header row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_type		object type
	* @access	private
	*/
	function addHeaderRow(&$a_tpl, $a_type)
	{
		if (!is_array($a_type))
		{
			$icon = ilUtil::getImagePath("icon_".$a_type.".gif");
			$title = $this->lng->txt("objs_".$a_type);
		}
		else
		{
			$icon = ilUtil::getImagePath("icon_lm.gif");
			$title = $this->lng->txt("learning_resources");
		}
		$a_tpl->setCurrentBlock("container_header_row");
		$a_tpl->setVariable("HEADER_IMG", $icon);
		$a_tpl->setVariable("BLOCK_HEADER_CONTENT", $title);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}

	/**
	* adds a standard row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_html		html code
	* @access	private
	*/
	function addStandardRow(&$a_tpl, $a_html, $a_item_id = "")
	{
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
			? "row_type_2"
			: "row_type_1";

		$a_tpl->touchBlock($this->cur_row_type);
		
		if ($_SESSION["il_cont_admin_panel"] == true)
		{
			/*
			$a_tpl->setCurrentBlock("block_row_check");
			$a_tpl->setVariable("ITEM_ID", $a_item_id);
			$a_tpl->parseCurrentBlock();*/
		}
		else
		{
			$a_tpl->setVariable("ROW_NBSP", "&nbsp;");
		}
		$a_tpl->setCurrentBlock("container_standard_row");
		$a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}

	function resetRowType()
	{
		$this->cur_row_type = "";
	}
	
	/**
	* returns a new list block template
	*
	* @access	private
	* @return	object		block template
	*/
	function &newBlockTemplate()
	{
		$tpl = new ilTemplate ("tpl.pd_list_block.html", true, true);
		$this->cur_row_type = "row_type_1";
		return $tpl;
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
                $this->tpl->setVariable("MAILCLASS", $mail["m_status"] == 'read' ? 'mailread' : 'mailunread');
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
	 * display New Mails
	 */


	function displayMails()
	{

		// MAILS
		// GET INBOX FOLDER FOR LINK_READ
		include_once "./include/inc.header.php";
		include_once "./include/inc.mail.php";
		include_once "classes/class.ilObjUser.php";
		include_once "classes/class.ilMailbox.php";
		include_once "classes/class.ilMail.php";


		// BEGIN MAILS
		$umail = new ilMail($_SESSION["AccountId"]);
		$mbox = new ilMailBox($_SESSION["AccountId"]);
		$inbox = $mbox->getInboxFolder();

		//SHOW MAILS FOR EVERY USER
		$mail_data = $umail->getMailsOfFolder($inbox);
		$mail_counter = $umail->getMailCounterData();
		$unreadmails = 0;
		

		foreach ($mail_data as $mail)
		{
			//ONLY NEW MAILS WOULD BE ON THE PERONAL DESKTOP
			if($mail["m_status"]== 'unread')
			{
				//echo $mail["m_status"];
				
				$this->tpl->setCurrentBlock("tbl_mails");
				$this->tpl->setVariable("ROWCOL",++$counter%2 ? 'tblrow1' : 'tblrow2');
				$this->tpl->setVariable("NEW_MAIL",$this->lng->txt("email"));

				// GET SENDER NAME
				$user = new ilObjUser($mail["sender_id"]);

				if(!($fullname = $user->getFullname()))
				{
					$fullname = $this->lng->txt("unknown");
				}


				$this->tpl->setCurrentBlock("tbl_mails");
				//columns headlines
				$this->tpl->setVariable("NEW_TXT_SENDER", $this->lng->txt("sender"));
				$this->tpl->setVariable("NEW_TXT_SUBJECT", $this->lng->txt("subject"));
				$this->tpl->setVariable("NEW_TXT_DATE",$this->lng->txt("date")."/".$this->lng->txt("time"));


				$this->tpl->setCurrentBlock("tbl_mails_row");
				$this->tpl->setVariable("NEW_MAIL_FROM", $fullname);
				$this->tpl->setVariable("NEW_MAILCLASS", $mail["status"] == 'read' ? 'mailread' : 'mailunread');
				$this->tpl->setVariable("NEW_MAIL_SUBJ", $mail["m_subject"]);
				$this->tpl->setVariable("NEW_MAIL_DATE", ilFormat::formatDate($mail["send_time"]));
				$target_name = htmlentities(urlencode("mail_read.php?mobj_id=".$inbox."&mail_id=".$mail["mail_id"]));
				$this->tpl->setVariable("NEW_MAIL_LINK_READ", "mail_frameset.php?target=".$target_name);
				$this->tpl->parseCurrentBlock();

			}
		}
	}




    /**
	 * display users online
	 */
    function displayUsersOnline()
    {
        global $ilias;

		$users_online_pref = $ilias->account->getPref("show_users_online");
        if ($users_online_pref != "y" && $users_online_pref != "associated")
        {

            return;
        }

        $this->tpl->setVariable("TXT_USERS_ONLINE",$this->lng->txt("users_online"));

        if ($users_online_pref == "associated")
		{
			$users = ilUtil::getAssociatedUsersOnline($ilias->account->getId());
		} else {
			$users = ilUtil::getUsersOnline();
		}

        $num = 0;

        foreach ($users as $user_id => $user)
        {
            if ($user_id != ANONYMOUS_USER_ID)
            {
                $num++;
            }
            else
            {
                $visitors = $user["num"];
            }
        }

        // parse visitors text
        if (empty($visitors) || $users_online_pref == "associated")
        {
            $visitor_text = "";
        }
        elseif ($visitors == "1")
        {
            $visitor_text = "1 ".$this->lng->txt("visitor");
        }
        else
        {
            $visitor_text = $visitors." ".$this->lng->txt("visitors");
        }

		// determine whether the user want's to see details of the active users
		// and remember user preferences, in case the user has changed them.
		$showdetails = false;
		if ($_GET['cmd'] == 'whoisdetail')
		{
			$ilias->account->writePref('show_users_online_details','y');
			$showdetails = true;
		}
		else if ($_GET['cmd'] == 'hidedetails')
		{
			$ilias->account->writePref('show_users_online_details','n');
			$showdetails = false;
		} 
		else
		{
			$showdetails = $ilias->account->getPref('show_users_online_details') == 'y';
		}


		// parse registered users text
		if ($num > 0)
		{
			$user_kind = ($users_online_pref == "associated") ? "associated_user" : "registered_user";
			if ($num == 1)
			{
				$user_list = $num." ".$this->lng->txt($user_kind);
			}

			else
			{
				$user_list = $num." ".$this->lng->txt($user_kind."s");
			}

			// add details link
			if ($showdetails)
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

			if (!empty($visitor_text))
			{
				$user_list .= " ".$this->lng->txt("and")." ".$visitor_text;
			}

			$user_list .= $user_details_link;
		}
		else
		{
			$user_list = $visitor_text;
		}

		$this->tpl->setVariable("USER_LIST",$user_list);

        // display details of users online
        if ($showdetails)
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
                    $user_obj = new ilObjUser($user_id);
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

                    // user image
                    $webspace_dir = ilUtil::getWebspaceDir();
                    $image_dir = $webspace_dir."/usr_images";
                    $xxthumb_file = $image_dir."/usr_".$user_obj->getID()."_xxsmall.jpg";
                    if ($user_obj->getPref("public_upload") == "y" &&
                        $user_obj->getPref("public_profile") == "y" &&
                        @is_file($xxthumb_file))
                    {
                        $this->tpl->setCurrentBlock("usr_image");
                        $this->tpl->setVariable("USR_IMAGE", $xxthumb_file."?t=".rand(1, 99999));
                        $this->tpl->parseCurrentBlock();
                    }
                    else
                    {
                        $this->tpl->setVariable("NO_IMAGE", "&nbsp;");
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
                $this->tpl->setVariable("TXT_USR",ucfirst($this->lng->txt("user")));
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

		if(ilBookmarkFolder::isRootFolder($_SESSION['ilCurBMFolder']) or !$_SESSION['ilCurBMFolder'])
		{
			$colspan = 2;
		}
 
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
            $this->tpl->setVariable("BM_TARGET", "");
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
                    $this->tpl->setVariable("BM_TARGET", "");
                    break;

                case "bm":
                    $this->tpl->setVariable("BM_TITLE", "<img border=\"0\" vspace=\"0\" align=\"left\" src=\"".
											ilUtil::getImagePath("icon_bm.gif")."\">&nbsp;".$bm_item["title"]);
                    $this->tpl->setVariable("BM_LINK", $bm_item["target"]);
                    $this->tpl->setVariable("BM_TARGET", "_blank");
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
 * @author       Muzaffar Altaf <maltaf@tzi.de>
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