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

require_once("classes/class.ilTableGUI.php");

/**
* Class ilRepositoryGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package core
*/
class ilRepositoryGUI
{
	var $lng;
	var $ilias;
	var $tpl;
	var $tree;
	var $rbacsystem;
	var $cur_ref_id;
	var $cmd;
	var $mode;

	/**
	* Constructor
	* @access	public
	*/
	function ilRepositoryGUI()
	{
		global $lng, $ilias, $tpl, $tree, $rbacsystem, $objDefinition, $_GET;

		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->tree =& $tree;
		$this->rbacsystem =& $rbacsystem;
		$this->objDefinition =& $objDefinition;

		if (!empty($_GET["ref_id"]))
		{
			$this->cur_ref_id = $_GET["ref_id"];
		}
		else
		{
			if (!empty($_SESSION["il_rep_ref_id"]))
			{
				$this->cur_ref_id = $_SESSION["il_rep_ref_id"];
			}
			else
			{
				$this->cur_ref_id = $this->tree->getRootId();
			}
		}

		if (!empty($_GET["set_mode"]))
		{
			$_SESSION["il_rep_mode"] = $_GET["set_mode"];
		}

		$this->mode = ($_SESSION["il_rep_mode"] != "")
			? $_SESSION["il_rep_mode"]
			: "flat";

		if (($this->mode == "tree") || !$tree->isInTree($this->cur_ref_id))
		{
			$this->cur_ref_id = $this->tree->getRootId();
		}

		$_SESSION["il_rep_ref_id"] = $this->cur_ref_id;

		$this->categories = array();
		$this->learning_resources = array();
		$this->forums = array();
		$this->groups = array();

	}

	function executeCommand()
	{
		$cmd = (!empty($_GET["cmd"]))
			? $_GET["cmd"]
			: "showList";

		if ($cmd == "post")
		{
			$cmd = key($_POST["cmd"]);
		}
		$this->cmd = $cmd;

		$this->$cmd();
	}

	function showList()
	{
		switch ($this->mode)
		{
			case "tree":
				$this->showTree();
				break;

			default:
				$this->showFlatList();
				break;
		}
	}

	function showTree()
	{
		// output objects
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.repository.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output tabs
		//$this->setTabs();

		// output locator
		$this->setLocator();

		// output message
		if($this->message)
		{
			sendInfo($this->message);
		}

		// display infopanel if something happened
		infoPanel();

		// set header
		$this->setHeader();

		$this->tpl->setCurrentBlock("content");
		$this->tpl->addBlockFile("OBJECTS", "objects", "tpl.rep_explorer.html");

		require_once ("classes/class.ilRepositoryExplorer.php");
		$exp = new ilRepositoryExplorer("repository.php?cmd=goto");
		$exp->setTargetGet("ref_id");

		if ($_GET["repexpand"] == "")
		{
			$expanded = $this->tree->readRootId();
		}
		else
		{
			$expanded = $_GET["repexpand"];
		}

		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setCurrentBlock("objects");
		//$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_chap_and_pages"));
		$this->tpl->setVariable("EXPLORER", $output);
		//$this->tpl->setVariable("ACTION", "repository.php?repexpand=".$_GET["repexpand"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}

	function showFlatList()
	{
		// get all objects of current node
		$objects = $this->tree->getChilds($this->cur_ref_id, "title");
		foreach($objects as $key => $object)
		{
			if (!$this->rbacsystem->checkAccess('visible',$object["child"]))
			{
				continue;
			}
			switch ($object["type"])
			{
				// categories
				case "cat":
					$this->categories[$key] = $object;
					break;

				// learning resources
				case "lm":
				//case "slm":
				case "dbk":
					$this->learning_resources[$key] = $object;

					// check if lm is online
					if ($object["type"] == "lm")
					{
						include_once("content/classes/class.ilObjLearningModule.php");
						$lm_obj =& new ilObjLearningModule($object["ref_id"]);
						if((!$lm_obj->getOnline()) && (!$this->rbacsystem->checkAccess('write',$object["child"])))
						{
							unset ($this->learning_resources[$key]);
						}
					}
					break;

				// forums
				case "frm":
					$this->forums[$key] = $object;
					break;

				// groups
				case "grp":
					$this->groups[$key] = $object;
					break;
			}
		}

		// output objects
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.repository.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output tabs
		//$this->setTabs();

		// output locator
		$this->setLocator();

		// output message
		if($this->message)
		{
			sendInfo($this->message);
		}

		// display infopanel if something happened
		infoPanel();

		// set header
		$this->setHeader();

		$this->tpl->setCurrentBlock("content");
		$this->tpl->addBlockFile("OBJECTS", "objects", "tpl.repository_lists.html");


		// (sub)categories
		if (count($this->categories))
		{
			$this->showCategories();
		}

		// learning resources
		if (count($this->learning_resources))
		{
			$this->showLearningResources();
		}

		// forums
		if (count($this->forums))
		{
			$this->showForums();
		}

		// groups
		if (count($this->groups))
		{
			$this->showGroups();
		}

		$this->tpl->show();
	}


	function setHeader()
	{
		if ($this->cur_ref_id == $this->tree->getRootId())
		{
			$this->tpl->setVariable("HEADER",  $this->lng->txt("repository"));
			if($this->mode != "tree")
			{
				$this->showPossibleSubObjects("root");
			}
		}
		else
		{
			require_once("classes/class.ilObjCategory.php");
			$cat =& new ilObjCategory($this->cur_ref_id, true);
			$this->tpl->setVariable("HEADER",  $cat->getTitle());
			$this->tpl->setVariable("H_DESCRIPTION",  $cat->getDescription());
			if($this->mode != "tree")
			{
				$this->showPossibleSubObjects("cat");
			}
		}
		$this->tpl->setVariable("H_FORMACTION",  "repository.php?ref_id=".$_GET["ref_id"].
			"&cmd=post");

		if ($this->cur_ref_id != $this->tree->getRootId())
		{
			$par_id = $this->tree->getParentId($this->cur_ref_id);
			$this->tpl->setCurrentBlock("top");
			$this->tpl->setVariable("LINK_TOP", "repository.php?ref_id=".$par_id);
			$this->tpl->setVariable("IMG_TOP",ilUtil::getImagePath("ic_top.gif"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("LINK_FLAT", "repository.php?set_mode=flat&ref_id=".$this->cur_ref_id);
		$this->tpl->setVariable("IMG_FLAT",ilUtil::getImagePath("ic_flatview.gif"));

		$this->tpl->setVariable("LINK_TREE", "repository.php?set_mode=tree&ref_id=".$this->cur_ref_id);
		$this->tpl->setVariable("IMG_TREE",ilUtil::getImagePath("ic_treeview.gif"));
	}

	/**
	* show categories
	*/
	function showCategories()
	{
		$tpl =& new ilTemplate ("tpl.table.html", true, true);

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_cat_row.html");

		// set offset & limit
		$offset = intval($_GET["offset"]);
		$limit = intval($_GET["limit"]);
		$limit = 9999;		// todo: not nice
		$maxcount = count($this->categories);
		$cats = array_slice($this->categories, $offset, $limit);

		// render table content data
		if (count($cats) > 0)
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_cat_row.html");

			// counter for rowcolor change
			$num = 0;

			foreach ($cats as $cat)
			{

				require_once("classes/class.ilObjCategory.php");
				$cat_obj =& new ilObjCategory($cat["ref_id"], true);

				$tpl->setCurrentBlock("tbl_content");

				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;

				$tpl->setVariable("CAT_IMG", ilUtil::getImagePath("icon_cat.gif"));

				$obj_link = "repository.php?ref_id=".$cat["ref_id"];
				$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("", "items[]", $cat["ref_id"]));
				if ($this->rbacsystem->checkAccess('read',$cat["ref_id"]))
				{
					$tpl->setCurrentBlock("cat_link");
					$tpl->setVariable("CAT_LINK", $obj_link);
					$tpl->setVariable("TITLE", $cat["title"]);
					$tpl->parseCurrentBlock();
				}
				else
				{
					$tpl->setCurrentBlock("cat_show");
					$tpl->setVariable("STITLE", $cat["title"]);
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("tbl_content");

				$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_cat"));
				$tpl->setVariable("DESCRIPTION", $cat_obj->getDescription());
				$tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($cat["last_update"]));
				$tpl->parseCurrentBlock();
			}
		}
		else
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.no_objects_row.html");
			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL", "tblrow1");
			$tpl->setVariable("COLSPAN", "3");
			$tpl->setVariable("TXT_NO_OBJECTS",$this->lng->txt("lo_no_content"));
			$tpl->parseCurrentBlock();
		}


		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("categories"),
			"icon_cat_b.gif", $this->lng->txt("categories"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array("", $this->lng->txt("type"), $this->lng->txt("title")));
		$tbl->setHeaderVars(array("", "type", "title"),
			array("ref_id" => $_GET["ref_id"]));
		$tbl->setColumnWidth(array("1%", "1%", "98%"));

		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($limit);
		$tbl->setOffset($offset);
		$tbl->setMaxCount($maxcount);

		// footer
		$tbl->setFooter("tblfooter", $this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("content");
		$tbl->disable("footer");

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		// parse it
		$tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("subcategories");
		$this->tpl->setVariable("CATEGORIES", $tpl->get());
		$this->tpl->parseCurrentBlock();

	}

	/**
	* show learning resources
	*/
	function showLearningResources()
	{
		// set offset & limit
		$offset = intval($_GET["offset"]);
		$limit = intval($_GET["limit"]);

		if ($limit == 0)
		{
			$limit = 9999;	// todo: not nice
		}

		$maxcount = count($this->learning_resources);
		$lrs = array_slice($this->learning_resources, $offset, $limit);

		$tpl =& new ilTemplate ("tpl.table.html", true, true);

		$lr_num = count($lrs);

		// render table content data
		if ($lr_num > 0)
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_lres_row.html");

			// counter for rowcolor change
			$num = 0;

			foreach ($lrs as $lr_data)
			{
				$tpl->setCurrentBlock("tbl_content");

				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;

				$obj_icon = "icon_".$lr_data["type"]."_b.gif";

				//$tpl->setVariable("TITLE", $lr_data["title"]);

				// learning modules
				if ($lr_data["type"] == "lm" || $lr_data["type"] == "dbk")
				{
					$obj_link = "content/lm_presentation.php?ref_id=".$lr_data["ref_id"];
					$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("","items[]",$lr_data["ref_id"]));
					if($this->rbacsystem->checkAccess('read',$lr_data["ref_id"]))
					{
						$tpl->setCurrentBlock("read");
						$tpl->setVariable("VIEW_LINK", $obj_link);
						$tpl->setVariable("VIEW_TARGET", "_top");
						$tpl->setVariable("R_TITLE", $lr_data["title"]);
						$tpl->parseCurrentBlock();
					}
					else
					{
						$tpl->setCurrentBlock("visible");
						$tpl->setVariable("V_TITLE", $lr_data["title"]);
						$tpl->parseCurrentBlock();
					}
					$tpl->setCurrentBlock("tbl_content");
					if($this->rbacsystem->checkAccess('write',$lr_data["ref_id"]))
					{
						$tpl->setVariable("EDIT_LINK","content/lm_edit.php?ref_id=".$lr_data["ref_id"]);
						$tpl->setVariable("EDIT_TARGET","bottom");
						$tpl->setVariable("TXT_EDIT", "(".$this->lng->txt("edit").")");
					}
					if (!$this->ilias->account->isDesktopItem($lr_data["ref_id"], "lm"))
					{
						if ($this->rbacsystem->checkAccess('read', $lr_data["ref_id"]))
						{
							$tpl->setVariable("TO_DESK_LINK", "repository.php?cmd=addToDesk&ref_id=".$_GET["ref_id"].
								"&item_ref_id=".$lr_data["ref_id"].
								"&type=lm&offset=".$_GET["offset"]."&sort_order=".$_GET["sort_order"].
								"&sort_by=".$_GET["sort_by"]);
							$tpl->setVariable("TXT_TO_DESK", "(".$this->lng->txt("to_desktop").")");
						}
					}
				}

				// scorm learning modules
				/*
				if ($lr_data["type"] == "slm")
				{
					$obj_link = "content/scorm_presentation.php?ref_id=".$lr_data["ref_id"];
					$tpl->setVariable("VIEW_LINK", $obj_link);
					$tpl->setVariable("VIEW_TARGET", "bottom");
				}*/

				$tpl->setVariable("IMG", $obj_icon);
				$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$lr_data["type"]));
				$tpl->setVariable("DESCRIPTION", $lr_data["description"]);
				$tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($lr_data["last_update"]));
				$tpl->parseCurrentBlock();
			}

		}
		else
		{

			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.no_objects_row.html");
			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL", "tblrow1");
			$tpl->setVariable("COLSPAN", "3");
			$tpl->setVariable("TXT_NO_OBJECTS",$this->lng->txt("lo_no_content"));
			$tpl->parseCurrentBlock();
		}

		//$this->showPossibleSubObjects("lrs");

		// create table
		$tbl = new ilTableGUI();
		$tbl->setTemplate($tpl);

		// title & header columns
		$tbl->setTitle($this->lng->txt("learning_resources"),"icon_lm_b.gif",$this->lng->txt("learning_resources"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array("", $this->lng->txt("type"), $this->lng->txt("title")));
		$tbl->setHeaderVars(array("", "type", "title"),
			array("ref_id" => $_GET["ref_id"]));
		$tbl->setColumnWidth(array("1%", "1%", "98%"));

		// control
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($limit);
		$tbl->setOffset($offset);
		$tbl->setMaxCount($maxcount);

		// footer
		//$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");
		//$tbl->disable("title");

		// render table
		$tbl->render();
		$tpl->parseCurrentBlock();


		$this->tpl->setCurrentBlock("learning_resources");
		$this->tpl->setVariable("LEARNING_RESOURCES", $tpl->get());
		//$this->tpl->setVariable("LEARNING_RESOURCES", "hh");
		$this->tpl->parseCurrentBlock();

	}


	/**
	* show forums
	*/
	function showForums()
	{
		global $lng, $rbacsystem, $ilias, $rbacreview;

		require_once "classes/class.ilForum.php";
		$frm =& new ilForum();
		$lng->loadLanguageModule("forum");

		$tpl =& new ilTemplate ("tpl.rep_forums.html", true, true);

		// get forums
		foreach($this->forums as $data)
		{
			unset($topicData);

			$frm->setWhereCondition("top_frm_fk = ".$data["obj_id"]);
			$topicData = $frm->getOneTopic();

			if ($topicData["top_num_threads"] > 0)
			{
				$thr_page = "liste";
			}
			else
			{
				$thr_page = "new";
			}


			$tpl->setCurrentBlock("forum_row");

			$tpl->setVariable("TXT_FORUMPATH", $lng->txt("context"));

			//$rowCol = ilUtil::switchColor($z,"tblrow2","tblrow1");
			//$tpl->setVariable("ROWCOL", $rowCol);

			$moderators = "";
			$lpCont = "";
			$lastPost = "";

			// get last-post data
			if ($topicData["top_last_post"] != "")
			{
				$lastPost = $frm->getLastPost($topicData["top_last_post"]);
				$lastPost["pos_message"] = $frm->prepareText($lastPost["pos_message"]);
			}
			// read-access
			// TODO: this will not work :-(
			// We have no ref_id at this point
			if ($rbacsystem->checkAccess("read", $data["ref_id"]))
			{

				// forum title
				if ($topicData["top_num_threads"] < 1 && (!$rbacsystem->checkAccess("write", $data["ref_id"])))
				{
					$tpl->setVariable("TITLE","<b>".$topicData["top_name"]."</b>");
				}
				else
				{
					$tpl->setVariable("TITLE","<a href=\"forums_threads_".$thr_page.".php?ref_id=".$data["ref_id"]."&backurl=forums\">".$topicData["top_name"]."</a>");
				}
				// add to desktop link
				if (!$ilias->account->isDesktopItem($data["ref_id"], "frm"))
				{
					$tpl->setVariable("TO_DESK_LINK", "repository.php?cmd=addToDesk&ref_id=".$_GET["ref_id"].
						"&item_ref_id=".$data["ref_id"].
						"&type=frm&offset=".$_GET["offset"]."&sort_order=".$_GET["sort_order"].
						"&sort_by=".$_GET["sort_by"]);

					$tpl->setVariable("TXT_TO_DESK", "(".$lng->txt("to_desktop").")");
				}
				// create-dates of forum
				if ($topicData["top_usr_id"] > 0)
				{
					$moderator = $frm->getUser($topicData["top_usr_id"]);

					$tpl->setVariable("TXT_MODERATORS", $lng->txt("forums_moderators"));
				}

				// when forum was changed ...
				if ($topicData["update_user"] > 0)
				{
					$moderator = $frm->getUser($topicData["update_user"]);

					$tpl->setVariable("LAST_UPDATE_TXT1", $lng->txt("last_change"));
					$tpl->setVariable("LAST_UPDATE_TXT2", strtolower($lng->txt("by")));
					$tpl->setVariable("LAST_UPDATE", $frm->convertDate($topicData["top_update"]));
					$tpl->setVariable("LAST_UPDATE_USER","<a href=\"forums_user_view.php?ref_id=".$this->cur_ref_id."&user=".$topicData["update_user"]."&backurl=repository&offset=".$Start."\">".$moderator->getLogin()."</a>");
				}

				// show content of last-post
				if (is_array($lastPost))
				{
					$lpCont = "<a href=\"forums_frameset.php?target=true&pos_pk=".$lastPost["pos_pk"]."&thr_pk=".$lastPost["pos_thr_fk"]."&ref_id=".$data["ref_id"]."#".$lastPost["pos_pk"]."\">".$lastPost["pos_message"]."</a><br/>".strtolower($lng->txt("from"))."&nbsp;";
					$lpCont .= "<a href=\"forums_user_view.php?ref_id=".$this->cur_ref_id."&user=".$lastPost["pos_usr_id"]."&backurl=repository&offset=".$Start."\">".$lastPost["login"]."</a><br/>";
					$lpCont .= $lastPost["pos_date"];
				}

				$tpl->setVariable("LAST_POST", $lpCont);

				// get dates of moderators
				if ($topicData["top_mods"] > 0)
				{
					$MODS = $rbacreview->assignedUsers($topicData["top_mods"]);

					for ($i = 0; $i < count($MODS); $i++)
					{
						unset($moderator);
						$moderator = $frm->getUser($MODS[$i]);

						if ($moderators != "")
						{
							$moderators .= ", ";
						}

						$moderators .= "<a href=\"forums_user_view.php?ref_id=".$this->cur_ref_id."&user=".$MODS[$i]."&backurl=repository&offset=".$Start."\">".$moderator->getLogin()."</a>";
					}
				}
				$tpl->setVariable("MODS",$moderators);
				$tpl->setVariable("TXT_MODERATORS", $lng->txt("forums_moderators"));

				$tpl->setVariable("FORUM_ID", $topicData["top_pk"]);

			} // if ($rbacsystem->checkAccess("read", $data["ref_id"]))
			else
			{
				// only visible-access
				$tpl->setVariable("TITLE","<b>".$topicData["top_name"]."</b>");

				if (is_array($lastPost))
				{
					$lpCont = $lastPost["pos_message"]."<br/>".$lng->txt("from")." ".$lastPost["lastname"]."<br/>".$lastPost["pos_date"];
				}

				$tpl->setVariable("LAST_POST", $lpCont);

				if ($topicData["top_mods"] > 0)
				{
					$MODS = $rbacreview->assignedUsers($topicData["top_mods"]);

					for ($i = 0; $i < count($MODS); $i++)
					{
						unset($moderator);
						$moderator = $frm->getUser($MODS[$i]);

						if ($moderators != "")
						{
							$moderators .= ", ";
						}

						$moderators .= $moderator->getLogin();
					}
				}
				$tpl->setVariable("MODS",$moderators);
				$tpl->setVariable("TXT_MODERATORS", $lng->txt("forums_moderators"));
			} // else

			// get context of forum
			$PATH = $frm->getForumPath($data["ref_id"]);
			$tpl->setVariable("FORUMPATH",$PATH);

			$tpl->setVariable("DESCRIPTION",$topicData["top_description"]);
			$tpl->setVariable("NUM_THREADS",$topicData["top_num_threads"]);
			$tpl->setVariable("NUM_POSTS",$topicData["top_num_posts"]);
			$tpl->setVariable("NUM_VISITS",$topicData["visits"]);

			$tpl->parseCurrentBlock();

		} // foreach($frm_obj as $data)

		/*
		$tpl->setCurrentBlock("forum_options");
		$tpl->setVariable("TXT_SELECT_ALL", $lng->txt("select_all"));
		$tpl->setVariable("IMGPATH", $tpl->tplPath);
		$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?ref_id=".$_GET["ref_id"]);
		$tpl->setVariable("TXT_OK",$lng->txt("ok"));
		//$tpl->setVariable("TXT_PRINT", $lng->txt("print"));
		$tpl->setVariable("TXT_EXPORT_HTML", $lng->txt("export_html"));
		$tpl->setVariable("TXT_EXPORT_XML", $lng->txt("export_xml"));
		$tpl->parseCurrentBlock();*/

		$tpl->setCurrentBlock("forums");
		$tpl->setVariable("COUNT_FORUM", $lng->txt("forums_count").": ".$frmNum);
		$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("forums_overview"));
		$tpl->setVariable("TXT_FORUM", $lng->txt("forum"));
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
		$tpl->setVariable("TXT_NUM_THREADS", $lng->txt("forums_threads"));
		$tpl->setVariable("TXT_NUM_POSTS", $lng->txt("forums_articles"));
		$tpl->setVariable("TXT_NUM_VISITS", $lng->txt("visits"));
		$tpl->setVariable("TXT_LAST_POST", $lng->txt("forums_last_post"));
		$tpl->setVariable("TXT_MODS", $lng->txt("forums_moderators"));
		$tpl->setVariable("FRM_IMG", ilUtil::getImagePath("icon_frm_b.gif"));
		$tpl->setVariable("FRM_TITLE", $lng->txt("forums"));
		$tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("forums");
		$this->tpl->setVariable("FORUMS", $tpl->get());
		$this->tpl->parseCurrentBlock();
	}


	/**
	* show groups
	*/
	function showGroups()
	{
		global  $tree, $rbacsystem;

		// set offset & limit
		//$offset = intval($_GET["offset"]);
		//$limit = intval($_GET["limit"]);

		$limit = 9999;
		$offset = 0;

		$tpl =& new ilTemplate("tpl.table.html", true, true);

		$tpl->setVariable("FORMACTION", "obj_location_new.php?new_type=grp&from=grp_list.php");
		$tpl->setVariable("FORM_ACTION_METHOD", "post");
		$tpl->setVariable("ACTIONTARGET", "bottom");

		$maxcount = count($this->groups);

		$cont_arr = array_slice($this->groups, $offset, $limit);

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_grp_row.html");

		$cont_num = count($cont_arr);

		// render table content data
		if ($cont_num > 0)
		{
			// counter for rowcolor change
			$num = 0;
			foreach ($cont_arr as $cont_data)
			{
				$tpl->setCurrentBlock("tbl_content");
				$newuser = new ilObjUser($cont_data["owner"]);
				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;
				$obj_link = "group.php?cmd=view&ref_id=".$cont_data["ref_id"];
				$obj_icon = "icon_".$cont_data["type"]."_b.gif";
				$tpl->setVariable("TITLE", $cont_data["title"]);
				$tpl->setVariable("LINK", $obj_link);
				$tpl->setVariable("LINK_TARGET", "bottom");
				$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("", "items[]", $cont_data["ref_id"]));
				//$tpl->setVariable("IMG", $obj_icon);
				//$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
				$tpl->setVariable("DESCRIPTION", $cont_data["description"]);
				$tpl->setVariable("OWNER", $newuser->getFullName($cont_data["owner"]));
				$tpl->setVariable("LAST_CHANGE", $cont_data["last_update"]);
				//$tpl->setVariable("CONTEXTPATH", $this->getContextPath($cont_data["ref_id"]));
				$tpl->parseCurrentBlock();
			}
		}
		else
		{
			$tpl->setCurrentBlock("no_content");
			$tpl->setVariable("TXT_MSG_NO_CONTENT",$this->lng->txt("group_not_available"));
			$tpl->parseCurrentBlock("no_content");
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("groups"),"icon_grp_b.gif",$this->lng->txt("groups"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array("",$this->lng->txt("title"),$this->lng->txt("owner")));
		$tbl->setHeaderVars(array("","title","owner"), array("ref_id"=>$_GET["ref_id"]));
		$tbl->setColumnWidth(array("1%","89%","10%"));
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($limit);
		$tbl->setOffset($offset);
		$tbl->setMaxCount($maxcount);
		//$this->showActions(true);

		// footer
		//$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setCurrentBlock("groups");
		$this->tpl->setVariable("GROUPS", $tpl->get());
		$this->tpl->parseCurrentBlock();
	}

	/**
	* set tabs
	*/
	function setTabs()
	{
		// set tabs
		// display different buttons depending on viewmod
		if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
		{
			$ftabtype = "tabactive";
			$ttabtype = "tabinactive";
		}
		else
		{
			$ftabtype = "tabinactive";
			$ttabtype = "tabactive";
		}

		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE", $ttabtype);
		$this->tpl->setVariable("TAB_TARGET", "bottom");
		$this->tpl->setVariable("TAB_LINK", "repository.php?viewmode=tree");
		$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("treeview"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE", $ftabtype);
		$this->tpl->setVariable("TAB_TARGET", "bottom");
		$this->tpl->setVariable("TAB_LINK", "repository.php?viewmode=flat");
		$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("flatview"));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* set Locator
	*/
	function setLocator()
	{
		$a_tree =& $this->tree;
		$a_id = $this->cur_ref_id;

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $a_tree->getPathFull($a_id);

		// this is a stupid workaround for a bug in PEAR:IT
		$modifier = 1;

		if (isset($_GET["obj_id"]))
		{
			$modifier = 0;
		}
		
		// ### AA 03.11.10 added new locator GUI class ###
		$i = 1;

		foreach ($path as $key => $row)
		{
			if ($key < count($path)-$modifier)
			{
				$this->tpl->touchBlock("locator_separator");
			}

			$this->tpl->setCurrentBlock("locator_item");
			if ($row["child"] != $a_tree->getRootId())
			{
				$this->tpl->setVariable("ITEM", $row["title"]);
			}
			else
			{
				$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
			}
			$this->tpl->setVariable("LINK_ITEM", "repository.php?ref_id=".$row["child"]);
			//$this->tpl->setVariable("LINK_TARGET", " target=\"bottom\" ");

			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("locator");
			
			// ### AA 03.11.10 added new locator GUI class ###
			// navigate locator
			if ($row["child"] != $a_tree->getRootId())
			{
				$ilias_locator->navigate($i++,$row["title"],"repository.php?ref_id=".$row["child"],"bottom");
			}
			else
			{
				$ilias_locator->navigate($i++,$this->lng->txt("repository"),"repository.php?ref_id=".$row["child"],"bottom");
			}
		}

		/*
		if (DEBUG)
		{
			$debug = "DEBUG: <font color=\"red\">".$this->type."::".$this->id."::".$_GET["cmd"]."</font><br/>";
		}

		$prop_name = $this->objDefinition->getPropertyName($_GET["cmd"],$this->type);

		if ($_GET["cmd"] == "confirmDeleteAdm")
		{
			$prop_name = "delete_object";
		}*/

		$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* show possible subobjects (pulldown menu)
	*
	* @access	public
	*/
	function showPossibleSubObjects($type)
	{
		$d = $this->objDefinition->getCreatableSubObjects($type);

		if (count($d) > 0)
		{
			foreach ($d as $row)
			{
			    $count = 0;
				if ($row["max"] > 0)
				{
					//how many elements are present?
					for ($i=0; $i<count($this->data["ctrl"]); $i++)
					{
						if ($this->data["ctrl"][$i]["type"] == $row["name"])
						{
						    $count++;
						}
					}
				}
				if ($row["max"] == "" || $count < $row["max"])
				{
					if (in_array($row["name"], array("lm", "grp", "frm", "cat")))
					{
						if ($this->rbacsystem->checkAccess("create", $this->cur_ref_id, $row["name"]))
						{
							$subobj[] = $row["name"];
						}
					}
				}
			}
		}

		if (is_array($subobj))
		{
			$this->tpl->parseCurrentBlock("commands");
			// possible subobjects
			$opts = ilUtil::formSelect("", "new_type", $subobj);
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "create");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	function showActionSelect(&$subobj)
	{
		$actions = array("edit" => $this->lng->txt("edit"),
			"addToDesk" => $this->lng->txt("to_desktop"),
			"export" => $this->lng->txt("export"));

		if(is_array($subobj))
		{
			if(in_array("dbk",$subobj) or in_array("lm",$subobj))
			{
				$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);

				$this->tpl->setCurrentBlock("tbl_action_select");
				$this->tpl->setVariable("SELECT_ACTION",ilUtil::formSelect("","action_type",$actions,false,true));
				$this->tpl->setVariable("BTN_NAME","action");
				$this->tpl->setVariable("BTN_VALUE",$this->lng->txt("submit"));
				$this->tpl->parseCurrentBlock();
			}
		}
	}

	function addToDesk()
	{
		if($_GET["item_ref_id"] and $_GET["type"])
		{
			$this->ilias->account->addDesktopItem($_GET["item_ref_id"], $_GET["type"]);
			$this->showList();
		}
		else
		{
			if($_POST["items"])
			{
				foreach($_POST["items"] as $item)
				{
					$tmp_obj =& $this->ilias->obj_factory->getInstanceByRefId($item);
					$this->ilias->account->addDesktopItem($item, $tmp_obj->getType());
					unset($tmp_obj);
				}
			}
			$this->showList();
		}
	}

	function executeAdminCommand()
	{
		$_GET["ref_id"] = $_GET["ref_id"];
		$id = $_GET["ref_id"];
		$cmd = $this->cmd;
		$new_type = $_POST["new_type"]
			? $_POST["new_type"]
			: $_GET["new_type"];

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.repository.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output locator
		$this->setLocator();

		// output message
		if($this->message)
		{
			sendInfo($this->message);
		}

		// display infopanel if something happened
		infoPanel();

		// set header
		$this->setHeader();

		if (!$this->rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			sendInfo($lng->txt("msg_no_perm_create_object1")." ".$lng->txt($new_type."_a")." ".$lng->txt("msg_no_perm_create_object2"));
		}
		else
		{
			$class_name = $this->objDefinition->getClassName($new_type);
			$module = $this->objDefinition->getModule($new_type);
			$module_dir = ($module == "") ? "" : $module."/";
			$class_constr = "ilObj".$class_name."GUI";
			include_once("./".$module_dir."classes/class.ilObj".$class_name."GUI.php");

			$obj = new $class_constr($data, $id, true, false);

			$method = $cmd."Object";
			$obj->setReturnLocation("save", "repository.php?ref_id=".$_GET["ref_id"]);
			$obj->setReturnLocation("cancel", "repository.php?ref_id=".$_GET["ref_id"]);
			$obj->setReturnLocation("addTranslation",
				"repository.php?cmd=".$_GET["mode"]."&entry=0&mode=session&ref_id=".$_GET["ref_id"]."&new_type=".$_GET["new_type"]);

			$obj->setFormAction("save","repository.php?cmd=post&mode=$cmd&ref_id=".$_GET["ref_id"]."&new_type=".$new_type);
			$obj->setTargetFrame("save", "bottom");
//echo $class_constr.":".$method."<br>";
			$obj->$method();
		}

		$this->tpl->show();
	}

	function save()
	{
		$this->executeAdminCommand();
	}

	function create()
	{
		$this->executeAdminCommand();
	}

	function cancel()
	{
		$this->executeAdminCommand();
	}

	function addTranslation()
	{
		$this->executeAdminCommand();
	}

} // END class ilRepository

?>
