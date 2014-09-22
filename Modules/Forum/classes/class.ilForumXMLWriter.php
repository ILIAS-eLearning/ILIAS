<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";
include_once "./Modules/Forum/classes/class.ilFileDataForum.php";
include_once "Services/MediaObjects/classes/class.ilObjMediaObject.php";
include_once "Services/RTE/classes/class.ilRTE.php";

/**
* XML writer class
*
* Class to simplify manual writing of xml documents.
* It only supports writing xml sequentially, because the xml document
* is saved in a string with no additional structure information.
* The author is responsible for well-formedness and validity
* of the xml document.
*
* @author Andreas Kordosz (akordosz@databay.de)
* @version $Id: class.ilExerciseXMLWriter.php,v 1.3 2005/11/04 12:50:24 smeyer Exp $
*
* @ingroup ModulesFile
*/
class ilForumXMLWriter extends ilXmlWriter
{
	var $forum_id = null;

	/**
	* constructor
	* @param	string	xml version
	* @param	string	output encoding
	* @param	string	input encoding
	* @access	public
	*/
	public function __construct()
	{
		parent::__construct();
	}


	function setForumId($id)
	{
		$this->forum_id = $id;
	}


	/**
	 * Set file target directories
	 *
	 * @param	string	relative file target directory
	 * @param	string	absolute file target directory
	 */
	function setFileTargetDirectories($a_rel, $a_abs)
	{
		$this->target_dir_relative = $a_rel;
		$this->target_dir_absolute = $a_abs;
	}

	function start()
	{
		global $ilDB;

		ilUtil::makeDir($this->target_dir_absolute."/objects");

		$query_frm = 'SELECT * FROM frm_settings fs '.
					'JOIN object_data od ON fs.obj_id = od.obj_id '.
					'JOIN frm_data ON top_frm_fk  = od.obj_id '.
					'WHERE fs.obj_id = '.$ilDB->quote($this->forum_id, 'integer');

		$res = $ilDB->query($query_frm);

		while( $row = $res->fetchRow(DB_FETCHMODE_OBJECT) )
		{
			break;
		}

		$this->xmlStartTag("Forum", null);

		$this->xmlElement("Id", null, (int)$row->top_pk);
		$this->xmlElement("ObjId", null, (int)$row->obj_id);
		$this->xmlElement("Title",  null, $row->title);
        $this->xmlElement("Description",  null, $row->description);
		$this->xmlElement("DefaultView",  null, (int)$row->default_view);
		$this->xmlElement("Pseudonyms",  null, (int)$row->anonymized);
		$this->xmlElement("Statistics",  null, (int)$row->statistics_enabled);
		$this->xmlElement("ThreadRatings",  null, (int)$row->thread_rating);
		$this->xmlElement("PostingActivation",  null, (int)$row->post_activation);
		$this->xmlElement("PresetSubject",  null, (int)$row->preset_subject);
		$this->xmlElement("PresetRe",  null, (int)$row->add_re_subject);
		$this->xmlElement("NotificationType",  null, $row->notification_type);
		$this->xmlElement("ForceNotification",  null, (int)$row->admin_force_noti);
		$this->xmlElement("ToggleNotification",  null, (int)$row->user_toggle_noti);
		$this->xmlElement("LastPost",  null, $row->top_last_post);
		$this->xmlElement("Moderator",  null, (int)$row->top_mods);
		$this->xmlElement("CreateDate",  null, $row->top_date);
		$this->xmlElement("UpdateDate",  null, $row->top_update);
		$this->xmlElement("UpdateUserId",  null, $row->update_user);
		$this->xmlElement("UserId",  null, (int)$row->top_usr_id);
		$this->xmlElement("AuthorId",  null, (int)$row->thr_author_id);

		$query_thr = "SELECT frm_threads.* ".
					" FROM frm_threads ".
					" INNER JOIN frm_data ON top_pk = thr_top_fk ".
					'WHERE top_frm_fk = '.$ilDB->quote($this->forum_id, 'integer');

		$res = $ilDB->query($query_thr);

		while( $row = $ilDB->fetchObject($res) )
		{
			$this->xmlStartTag("Thread");

			$this->xmlElement("Id", null, (int)$row->thr_pk);
			$this->xmlElement("Subject", null, $row->thr_subject);
			$this->xmlElement("UserId", null, (int)$row->thr_display_user_id);
			$this->xmlElement("AuthorId", null, (int)$row->thr_author_id);
			$this->xmlElement("Alias", null, $row->thr_usr_alias);
			$this->xmlElement("LastPost", null, $row->thr_last_post);
			$this->xmlElement("CreateDate", null, $row->thr_date);
			$this->xmlElement("UpdateDate", null, $row->thr_date);
			$this->xmlElement("ImportName", null, $row->import_name);
			$this->xmlElement("Sticky", null, (int)$row->is_sticky);
			$this->xmlElement("Closed", null, (int)$row->is_closed);

			$query = 'SELECT frm_posts.*, frm_posts_tree.*
						FROM frm_posts
							INNER JOIN frm_data
								ON top_pk = pos_top_fk
							INNER JOIN frm_posts_tree
								ON pos_fk = pos_pk
						WHERE pos_thr_fk = '.$ilDB->quote($row->thr_pk, 'integer').' ';
			$query .= " ORDER BY frm_posts_tree.lft ASC";
			$resPosts = $ilDB->query($query);

			$lastDepth = null;
			while( $rowPost = $ilDB->fetchObject($resPosts) )
			{
				/*
				// Used for nested postings
				if( $rowPost->depth < $lastDepth )
				{
					for( $i = $rowPost->depth; $i <= $lastDepth; $i++ )
					{
						$this->xmlEndTag("Post");
					}
				}*/

				$this->xmlStartTag("Post");
				$this->xmlElement("Id", null, (int)$rowPost->pos_pk);
				$this->xmlElement("UserId", null, (int)$rowPost->pos_display_user_id);
				$this->xmlElement("AuthorId", null, (int)$rowPost->pos_author_id);
				$this->xmlElement("Alias", null, $rowPost->pos_usr_alias);
				$this->xmlElement("Subject", null, $rowPost->pos_subject);
				$this->xmlElement("CreateDate", null, $rowPost->pos_date);
				$this->xmlElement("UpdateDate", null, $rowPost->pos_update);
				$this->xmlElement("UpdateUserId", null, (int)$rowPost->update_user);
				$this->xmlElement("Censorship", null, (int)$rowPost->pos_cens);
				$this->xmlElement("CensorshipMessage", null, $rowPost->pos_cens_com);
				$this->xmlElement("Notification", null, $rowPost->notify);
				$this->xmlElement("ImportName", null, $rowPost->import_name);
				$this->xmlElement("Status", null, (int)$rowPost->pos_status);
				$this->xmlElement("Message", null, ilRTE::_replaceMediaObjectImageSrc($rowPost->pos_message, 0));

				if($rowPost->is_author_moderator === NULL)
				{
					$is_moderator_string = 'NULL';
				}	
				else
				{
					$is_moderator_string = (string)$rowPost->is_author_moderator;
				}
				
				$this->xmlElement("isAuthorModerator", null, $is_moderator_string);

				$media_exists = false;
				$mobs = ilObjMediaObject::_getMobsOfObject('frm:html', $rowPost->pos_pk);
				foreach($mobs as $mob)
				{
					$moblabel = "il_" . IL_INST_ID . "_mob_" . $mob;
					if(ilObjMediaObject::_exists($mob))
					{
						if(!$media_exists)
						{
							$this->xmlStartTag("MessageMediaObjects");
							$media_exists = true;
						}
						
						$mob_obj  = new ilObjMediaObject($mob);
						$imgattrs = array(
							"label"                 => $moblabel,
							"uri"                   => $this->target_dir_relative . "/objects/" . "il_" . IL_INST_ID . "_mob_" . $mob . "/" . $mob_obj->getTitle()
						);

						$this->xmlElement("MediaObject", $imgattrs, NULL);
						$mob_obj->exportFiles($this->target_dir_absolute);
					}
				}
				if($media_exists)
				{
					$this->xmlEndTag("MessageMediaObjects");
				}

				$this->xmlElement("Lft", null, (int)$rowPost->lft);
				$this->xmlElement("Rgt", null, (int)$rowPost->rgt);
				$this->xmlElement("Depth", null, (int)$rowPost->depth);
				$this->xmlElement("ParentId", null, (int)$rowPost->parent_pos);

				$tmp_file_obj = new ilFileDataForum(
					$this->forum_id, $rowPost->pos_pk
				);

				$set = array();
				if ( count($tmp_file_obj->getFilesOfPost()) )
				{
					foreach ($tmp_file_obj->getFilesOfPost() as $file)
					{
						$this->xmlStartTag("Attachment");

						copy($file['path'], $this->target_dir_absolute."/".basename($file['path']));
						$content = $this->target_dir_relative."/".basename($file['path']);
						$this->xmlElement("Content", null, $content);

						$this->xmlEndTag("Attachment");
					}
				}

				//Used for nested postings
				//$lastDepth = $rowPost->depth;

				$this->xmlEndTag("Post");
			}
			/*
			// Used for nested postings
			if( $lastDepth )
			{
				for( $i = 1; $i <= $lastDepth ; $i++ )
				{
					$this->xmlEndTag("Post");
				}

				$lastDepth = null;
			}*/
			$this->xmlEndTag("Thread");
		}
		$this->xmlEndTag("Forum");

		return true;
	}

	function getXML()
	{
		// Replace ascii code 11 characters because of problems with xml sax parser
	    return str_replace('&#11;', '', $this->xmlDumpMem(false));
	}
}