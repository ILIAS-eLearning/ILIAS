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

require_once './Modules/Forum/classes/class.ilForumProperties.php';

/**
* Forum export to HTML and Print.
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @ingroup ModulesForum
*/
class ilForumExportGUI
{
	function ilForumExportGUI()
	{
		global $lng, $ilCtrl, $ilUser;

		require_once './Modules/Forum/classes/class.ilForum.php';

		$this->frm = new ilForum();
		
		$this->ctrl = $ilCtrl;
		$lng->loadLanguageModule('forum');
	}

	/**
	* Execute Command.
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		switch($next_class)
		{
			default:
				return $this->$cmd();
				break;
		}
	}

	/**
	* Print Thread.
	*
	*/
	function printThread()
	{
		global $tpl, $lng, $ilDB, $ilUser, $ilAccess, $ilias;

		if (!$ilAccess->checkAccess('read,visible', '', $_GET['ref_id']))
		{
			$ilias->raiseError($lng->txt('permission_denied'), $ilias->error_obj->MESSAGE);
		}
		
		$tplEx = new ilTemplate('tpl.forums_export_print.html', true, true, 'Modules/Forum');
		$tplEx->setVariable('CSSPATH', $tpl->tplPath);
		
		// get forum- and thread-data
		$this->frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array($_GET['thr_top_fk']));
		
		if (is_array($frmData = $this->frm->getOneTopic()))
		{
			$objCurrentTopic = new ilForumTopic(addslashes($_GET['print_thread']), $ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']));	
			$objFirstPostNode = $objCurrentTopic->getFirstPostNode();			
			$postTree = $objCurrentTopic->getPostTree($objFirstPostNode);
			$posNum = count($postTree);
			
			// headline
			$tplEx->setVariable('HEADLINE', $lng->txt('forum').': '.$frmData['top_name'].' > '.
								$lng->txt('forums_thread').': '.$objCurrentTopic->getSubject().' > '.
								$lng->txt('forums_count_art').': '.$posNum);			
			
			$z = 0;
			foreach ($postTree as $post)
			{
				$tplEx->setCurrentBlock('posts_row');
				$rowCol = ilUtil::switchColor($z++, 'tblrow2', 'tblrow1');
				$tplEx->setVariable('ROWCOL', $rowCol);
				
				// forum properties
				$objForumProperties = ilForumProperties::getInstance($frmData['top_frm_fk']);				
				if ($objForumProperties->isAnonymized())
				{
					// if anonymized
					if ($post->getUserAlias() != '') $tplEx->setVariable('AUTHOR', $post->getUserAlias());
					else $tplEx->setVariable('AUTHOR', $lng->txt('forums_anonymous'));
				}
				else
				{
					// get author data					
					$author = $this->frm->getUserData($post->getUserId(), $post->getImportName());
					$tplEx->setVariable('AUTHOR', $author['login']);
				}
				
				if ($post->getUserId())
				{
					// get create- and update-dates
					if ($post->getUpdateUserId() > 0)
					{
						$last_user_data = $this->frm->getUserData($post->getUpdateUserId());
						
						$tplEx->setVariable('POST_UPDATE', "<br />[".$lng->txt("edited_at").": ".
											$this->frm->convertDate($post->getChangeDate())." - ".strtolower($lng->txt("from"))." ".$last_user_data['login']."]");
					}
					if ($author['public_profile'] != 'n')
					{
						$tplEx->setVariable('TXT_REGISTERED', $lng->txt('registered_since').':');
						$tplEx->setVariable('REGISTERED_SINCE', $this->frm->convertDate($author['create_date']));
					}
					
					if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
					{
						$numPosts = $this->frm->countUserArticles($post->getUserId());
					}
					else
					{
						$numPosts = $this->frm->countActiveUserArticles($post->getUserId());	
					}					
					
					$tplEx->setVariable('TXT_NUM_POSTS', $lng->txt('forums_posts').':');
					$tplEx->setVariable('NUM_POSTS', $numPosts);
				}
				
				$tplEx->setVariable('SUBJECT', $post->getSubject());
				$tplEx->setVariable('TXT_CREATE_DATE', $lng->txt('forums_thread_create_date'));
				$tplEx->setVariable('POST_DATE', $this->frm->convertDate($post->getCreateDate()));
				$tplEx->setVariable('SPACER', "<hr noshade width=\"100%\" size=\"1\" align=\"center\" />");

				if ($post->isCensored() > 0)
				{
					$tplEx->setVariable('POST', nl2br(stripslashes($post->getCensorshipComment())));
				}
				else
				{
					$tplEx->setVariable('POST', nl2br($this->frm->prepareText($post->getMessage(), 0, '', 'export')));	
				}
				$tplEx->parseCurrentBlock('posts_row');
				
				unset($author);	
				
			} // foreach ($postTree as $post)			
			
			$tplEx->setCurrentBlock('posttable');			
			$tplEx->setVariable('TXT_AUTHOR', $lng->txt('author'));		
			$tplEx->setVariable('TXT_POST', $lng->txt('forums_thread').': '.$objCurrentTopic->getSubject());	
			$tplEx->parseCurrentBlock('posttable');
			
		} // if (is_array($frmData = $this->frm->getOneTopic()))
		
		$tplEx->show();
	}	
	
	/**
	* Print Posting.
	*
	*/
	function printPost()
	{
		global $tpl, $lng, $ilDB, $ilAccess, $ilias;
		
		if (!$ilAccess->checkAccess('read,visible', '', $_GET['ref_id']))
		{
			$ilias->raiseError($lng->txt('permission_denied'), $ilias->error_obj->MESSAGE);
		}
		
		$tplEx = new ilTemplate('tpl.forums_export_print.html', true, true, 'Modules/Forum');
		$tplEx->setVariable('CSSPATH', $tpl->tplPath);
				
		// get forum- and thread-data
		$this->frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array($_GET['top_pk']));
		
		if (is_array($frmData = $this->frm->getOneTopic()))
		{
			// post object			
			$post = new ilForumPost(addslashes($_GET['print_post']));			
			
			// headline
			$tplEx->setVariable('HEADLINE', $lng->txt('forum').': '.$frmData['top_name'].' > '.
								$lng->txt('forums_thread').': '.$post->getThread()->getSubject());
			
						
			$tplEx->setCurrentBlock('posts_row');			
			$tplEx->setVariable('ROWCOL', 'tblrow2');
			
			$objForumProperties = ilForumProperties::getInstance($frmData['top_frm_fk']);				
			if ($objForumProperties->isAnonymized())
			{
				if ($post->getUserAlias() != '') $tplEx->setVariable('AUTHOR', $post->getUserAlias());
				else $tplEx->setVariable('AUTHOR', $lng->txt('forums_anonymous'));
			}
			else
			{
				// get author data				
				$author = $this->frm->getUserData($post->getUserId(), $post->getImportName());
				$tplEx->setVariable('AUTHOR', $author['login']);
			} 
			

			if ($post->getUserId())
			{
				// get create- and update-dates
				if ($post->getUpdateUserId())
				{
					$lastuserdata = $this->frm->getUserData($post->getUpdateUserId());
					$tplEx->setVariable('POST_UPDATE', "<br />[".$lng->txt('edited_at').": ".
										$this->frm->convertDate($post->getChangeDate())." - ".strtolower($lng->txt('from'))." ".$lastuserdata['login']."]");
				}
				
				if ($author['public_profile'] != 'n')
				{
					$tplEx->setVariable('TXT_REGISTERED', $lng->txt('registered_since'));
					$tplEx->setVariable('REGISTERED_SINCE', $this->frm->convertDate($author['create_date']));
				}				

				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{
					$numPosts = $this->frm->countUserArticles($post->getUserId());
				}
				else
				{
					$numPosts = $this->frm->countActiveUserArticles($post->getUserId());	
				}
				$tplEx->setVariable('TXT_NUM_POSTS', $lng->txt('forums_posts'));
				$tplEx->setVariable('NUM_POSTS', $numPosts);
			}
			
			$tplEx->setVariable('SUBJECT', $post->getSubject());
			$tplEx->setVariable('TXT_CREATE_DATE', $lng->txt('forums_thread_create_date'));
			$tplEx->setVariable('POST_DATE', $this->frm->convertDate($post->getCreateDate()));
			$tplEx->setVariable('SPACER', "<hr noshade width=\"100%\" size=\"1\" align=\"center\" />");

			if ($post->isCensored())
			{
				$tplEx->setVariable('POST', nl2br(stripslashes($post->getCensorshipComment())));
			}
			else
			{
				$tplEx->setVariable('POST', nl2br($this->frm->prepareText($post->getMessage(), 0, '', 'export')));	
			}

			$tplEx->parseCurrentBlock('posts_row');				
			$tplEx->setCurrentBlock('posttable');			
			$tplEx->setVariable('TXT_AUTHOR', $lng->txt('author'));		
			$tplEx->setVariable('TXT_POST', $lng->txt('forums_thread').': '.$post->getThread()->getSubject());	
			$tplEx->parseCurrentBlock('posttable');
			
		} // if (is_array($frmData = $this->frm->getOneTopic()))	
		
		$tplEx->show();		
	}	

	/**
	* Export to HTML.
	*
	*/
	function exportHTML()
	{
		global $lng, $tpl, $ilDB, $ilUser, $ilAccess, $ilias;
		
		if (!$ilAccess->checkAccess('read,visible', '', $_GET['ref_id']))
		{
			$ilias->raiseError($lng->txt('permission_denied'), $ilias->error_obj->MESSAGE);
		}

		$tplEx = new ilTemplate('tpl.forums_export_html.html', true, true, 'Modules/Forum');

		// threads
		for ($j = 0; $j < count($_POST['forum_id']); $j++)
		{	
			$objCurrentTopic = new ilForumTopic(addslashes($_POST['forum_id'][$j]), $ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']));

			// get forum- and thread-data
			$this->frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array($objCurrentTopic->getForumId()));
			
			if (is_array($frmData = $this->frm->getOneTopic()))
			{				
				$objFirstPostNode = $objCurrentTopic->getFirstPostNode();
				$postTree = $objCurrentTopic->getPostTree($objFirstPostNode);
				$posNum = count($postTree);
				
				$z = 0;
				foreach ($postTree as $post)
				{
					$tplEx->setCurrentBlock('posts_row');
					$rowCol = ilUtil::switchColor($z++, 'tblrow2', 'tblrow1');
					$tplEx->setVariable('ROWCOL', $rowCol);	
					

					$objForumProperties = ilForumProperties::getInstance($frmData['top_frm_fk']);				
					if ($objForumProperties->isAnonymized())
					{
						// if anonymized
						if ($post->getUserAlias() != '') $tplEx->setVariable('AUTHOR', $post->getUserAlias());
						else $tplEx->setVariable('AUTHOR', $lng->txt('forums_anonymous'));
					}
					else
					{	
						// get author data						
						$author = $this->frm->getUserData($post->getUserId(), $post->getImportName());
						$tplEx->setVariable('AUTHOR', $author['login']);
					}
					
					if ($post->getUserId())
					{
						// get create- and update-dates
						if ($post->getUpdateUserId())
						{
							$lastuserdata = $this->frm->getUserData($post->getUpdateUserId());
							$tplEx->setVariable('POST_UPDATE', "<br />[".$lng->txt('edited_at').": ".
												$this->frm->convertDate($post->getChangeDate())." - ".strtolower($lng->txt('from'))." ".
												$lastuserdata['login']."]");
						}
						
						if ($author['public_profile'] != 'n')
						{
							$tplEx->setVariable('TXT_REGISTERED', $lng->txt('registered_since'));
							$tplEx->setVariable('REGISTERED_SINCE', $this->frm->convertDate($author['create_date']));
						}	
						
						if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
						{
							$numPosts = $this->frm->countUserArticles($post->getUserId());
						}
						else
						{
							$numPosts = $this->frm->countActiveUserArticles($post->getUserId());
						}
						
						$tplEx->setVariable('TXT_NUM_POSTS', $lng->txt('forums_posts'));
						$tplEx->setVariable('NUM_POSTS', $numPosts);
					}
					
					$tplEx->setVariable('SUBJECT', $post->getSubject());
					$tplEx->setVariable('TXT_CREATE_DATE', $lng->txt('forums_thread_create_date'));
					$tplEx->setVariable('POST_DATE', $this->frm->convertDate($post->getCreateDate()));
					$tplEx->setVariable('SPACER', "<hr noshade width=\"100%\" size=\"1\" align=\"center\" />");

					if ($post->isCensored())
					{
						$tplEx->setVariable('POST', nl2br(stripslashes($post->getCensorshipComment())));
					}
					else
					{
						$tplEx->setVariable('POST', nl2br($this->frm->prepareText($post->getMessage(), 0, '', 'export')));	
					}

					$tplEx->parseCurrentBlock('posts_row');	
					
					unset($author);
				} // foreach ($postTree as $post)				
				
				$tplEx->setCurrentBlock('posttable');			
				$tplEx->setVariable('TXT_AUTHOR', $lng->txt('author'));		
				$tplEx->setVariable('TXT_POST', $lng->txt('forums_thread').': '.$objCurrentTopic->getSubject());	
				$tplEx->parseCurrentBlock('posttable');
				
				// Thread Headline
				$tplEx->setCurrentBlock('thread_headline');			
				$tplEx->setVariable('T_TITLE', $objCurrentTopic->getSubject());
				if ($ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']))
				{
					$tplEx->setVariable('T_NUM_POSTS', $objCurrentTopic->countPosts());	
				}
				else
				{
					$tplEx->setVariable('T_NUM_POSTS', $objCurrentTopic->countActivePosts());
				}
					
				$tplEx->setVariable('T_NUM_VISITS', $objCurrentTopic->getVisits());
				$tplEx->setVariable('T_FORUM', $frmData['top_name']);
									
				$objForumProperties = ilForumProperties::getInstance($frmData['top_frm_fk']);				
				if ($objForumProperties->isAnonymized())
				{					
					unset($t_author);
					if ($objCurrentTopic->getUserAlias() != '') $tplEx->setVariable('T_AUTHOR', $objCurrentTopic->getUserAlias());
					else $tplEx->setVariable('T_AUTHOR', $lng->txt('forums_anonymous'));
				}
				else
				{
					unset($t_author);
					$t_author = $this->frm->getUser($objCurrentTopic->getUserId());	
					$tplEx->setVariable('T_AUTHOR', $t_author->getLogin());
				} 
				
				$tplEx->setVariable('T_TXT_FORUM', $lng->txt('forum').': ');					
				$tplEx->setVariable('T_TXT_TOPIC', $lng->txt('forums_thread').': ');
				$tplEx->setVariable('T_TXT_AUTHOR', $lng->txt('forums_thread_create_from').': ');
				$tplEx->setVariable('T_TXT_NUM_POSTS', $lng->txt('forums_articles').': ');
				$tplEx->setVariable('T_TXT_NUM_VISITS', $lng->txt('visits').': ');
				
				$tplEx->parseCurrentBlock('thread_headline');
				
				$tplEx->setCurrentBlock('thread_block');	
				$tplEx->parseCurrentBlock('thread_block');					
				
				
				$tplEx->setCurrentBlock('forum_block');	
				$tplEx->parseCurrentBlock('forum_block');										
						
			} // if (is_array($frmData = $this->frm->getOneTopic()))			
		} // for ($j = 0; $j < count($_POST["forum_id"]); $j++)
		
		ilUtil::deliverData($tplEx->get(), 'forum_html_export_'.$_GET['ref_id'].'.html');
		exit();
	}
}
?>