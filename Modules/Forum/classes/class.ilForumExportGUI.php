<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Forum/classes/class.ilForumProperties.php';
require_once 'Services/RTE/classes/class.ilRTE.php';
require_once 'Modules/Forum/classes/class.ilForumAuthorInformation.php';
require_once 'Modules/Forum/classes/class.ilForum.php';

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
	const MODE_EXPORT_WEB    = 1;
	const MODE_EXPORT_CLIENT = 2;

	/**
	 * @var bool
	 */
	protected $is_moderator = false;

	/**
	 * @var ilForum
	 */
	protected $frm;

	/**
	 * 
	 */
	public function __construct()
	{
		global $lng, $ilCtrl, $ilAccess;

		$forum = new ilObjForum((int)$_GET['ref_id']);
		$this->frm = $forum->Forum;
		$this->frm->setForumId($forum->getId());
		$this->frm->setForumRefId($forum->getRefId());
		
		$this->ctrl = $ilCtrl;
		$lng->loadLanguageModule('forum');

		$this->is_moderator = $ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']);
	}

	/**
	 * 
	 */
	public function executeCommand()
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
	 * 
	 */
	public function printThread()
	{
		/**
		 * @var $tpl      ilTemplate
		 * @var $lng      ilLanguage
		 * @var $ilAccess ilAccessHandler
		 * @var $ilias    ILIAS
		 */
		global $tpl, $lng, $ilAccess, $ilias;

		if(!$ilAccess->checkAccess('read,visible', '', $_GET['ref_id']))
		{
			$ilias->raiseError($lng->txt('permission_denied'), $ilias->error_obj->MESSAGE);
		}
		
		ilDatePresentation::setUseRelativeDates(false);

		$tpl                 = new ilTemplate('tpl.forums_export_print.html', true, true, 'Modules/Forum');
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$tpl->setVariable('LOCATION_STYLESHEET', $location_stylesheet);

		require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
		iljQueryUtil::initjQuery();

		$this->frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array((int)$_GET['thr_top_fk']));
		if(is_array($frmData = $this->frm->getOneTopic()))
		{
			$topic = new ilForumTopic(addslashes($_GET['print_thread']), $this->is_moderator);

			$topic->setOrderField('frm_posts_tree.rgt');
			$first_post      = $topic->getFirstPostNode();
			$post_collection = $topic->getPostTree($first_post);
			$num_posts       = count($post_collection);

			$tpl->setVariable('TITLE', $topic->getSubject());
			$tpl->setVariable(
				'HEADLINE',
				$lng->txt('forum') . ': ' . $frmData['top_name'] . ' > ' .
				$lng->txt('forums_thread') . ': ' . $topic->getSubject() . ' > ' .
				$lng->txt('forums_count_art') . ': ' . $num_posts);

			$z = 0;
			foreach($post_collection as $post)
			{
				$this->renderPostHtml($tpl, $post, $z++, self::MODE_EXPORT_WEB);
			}
		}
		$tpl->show();
	}
	
	/**
	 *
	 */
	public function printPost()
	{
		/**
		 * @var $tpl      ilTemplate
		 * @var $lng      ilLanguage
		 * @var $ilAccess ilAccessHandler
		 * @var $ilias    ILIAS
		 */
		global $tpl, $lng, $ilAccess, $ilias;

		if(!$ilAccess->checkAccess('read,visible', '', $_GET['ref_id']))
		{
			$ilias->raiseError($lng->txt('permission_denied'), $ilias->error_obj->MESSAGE);
		}

		ilDatePresentation::setUseRelativeDates(false);

		$tpl                 = new ilTemplate('tpl.forums_export_print.html', true, true, 'Modules/Forum');
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$tpl->setVariable('LOCATION_STYLESHEET', $location_stylesheet);

		require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
		iljQueryUtil::initjQuery();

		$this->frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array((int)$_GET['top_pk']));
		if(is_array($frmData = $this->frm->getOneTopic()))
		{
			$post = new ilForumPost((int)$_GET['print_post'], $this->is_moderator);

			$tpl->setVariable('TITLE', $post->getThread()->getSubject());
			$tpl->setVariable('HEADLINE', $lng->txt('forum').': '.$frmData['top_name'].' > '. $lng->txt('forums_thread').': '.$post->getThread()->getSubject());

			$this->renderPostHtml($tpl, $post, 0, self::MODE_EXPORT_WEB);
		}
		$tpl->show();
	}

	/**
	 * 
	 */
	public function exportHTML()
	{
		/**
		 * @var $tpl      ilTemplate
		 * @var $lng      ilLanguage
		 * @var $ilAccess ilAccessHandler
		 * @var $ilias    ILIAS
		 */
		global $lng, $tpl, $ilAccess, $ilias;

		if(!$ilAccess->checkAccess('read,visible', '', $_GET['ref_id']))
		{
			$ilias->raiseError($lng->txt('permission_denied'), $ilias->error_obj->MESSAGE);
		}

		ilDatePresentation::setUseRelativeDates(false);

		$tpl = new ilTemplate('tpl.forums_export_html.html', true, true, 'Modules/Forum');
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$tpl->setVariable('LOCATION_STYLESHEET', $location_stylesheet);
		$tpl->setVariable('BASE', (substr(ILIAS_HTTP_PATH, -1) == '/' ? ILIAS_HTTP_PATH : ILIAS_HTTP_PATH . '/'));

		$num_threads  = count((array)$_POST['thread_ids']);
		for($j = 0; $j < $num_threads; $j++)
		{
			$topic = new ilForumTopic((int)$_POST['thread_ids'][$j], $this->is_moderator);

			$this->frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array($topic->getForumId()));
			if(is_array($thread_data = $this->frm->getOneTopic()))
			{
				if(0 == $j)
				{
					$tpl->setVariable('TITLE', $thread_data['top_name']);
				}

				$first_post = $topic->getFirstPostNode();
				$topic->setOrderField('frm_posts_tree.rgt');
				$post_collection = $topic->getPostTree($first_post);

				$z = 0;
				foreach($post_collection as $post)
				{
					$this->renderPostHtml($tpl, $post, $z++, self::MODE_EXPORT_CLIENT);
				}

				$tpl->setCurrentBlock('thread_headline');
				$tpl->setVariable('T_TITLE', $topic->getSubject());
				if($this->is_moderator)
				{
					$tpl->setVariable('T_NUM_POSTS', $topic->countPosts());
				}
				else
				{
					$tpl->setVariable('T_NUM_POSTS', $topic->countActivePosts());
				}
				$tpl->setVariable('T_NUM_VISITS', $topic->getVisits());
				$tpl->setVariable('T_FORUM', $thread_data['top_name']);
				$authorinfo = new ilForumAuthorInformation(
					$topic->getThrAuthorId(),
					$topic->getDisplayUserId(),
					$topic->getUserAlias(),
					$topic->getImportName()
				);
				$tpl->setVariable('T_AUTHOR', $authorinfo->getAuthorName());
				$tpl->setVariable('T_TXT_FORUM', $lng->txt('forum') . ': ');
				$tpl->setVariable('T_TXT_TOPIC', $lng->txt('forums_thread') . ': ');
				$tpl->setVariable('T_TXT_AUTHOR', $lng->txt('forums_thread_create_from') . ': ');
				$tpl->setVariable('T_TXT_NUM_POSTS', $lng->txt('forums_articles') . ': ');
				$tpl->setVariable('T_TXT_NUM_VISITS', $lng->txt('visits') . ': ');
				$tpl->parseCurrentBlock();
			}
			
			$tpl->setCurrentBlock('thread_block');
			$tpl->parseCurrentBlock();
		}

		ilUtil::deliverData($tpl->get('DEFAULT', false, false, false, true, false, false), 'forum_html_export_' . $_GET['ref_id'] . '.html');
	}

	/**
	 * @param ilTemplate $tpl
	 * @param ilForumPost $post
	 * @param int $counter
	 * @param int $mode
	 */
	protected function renderPostHtml(ilTemplate $tpl, ilForumPost $post, $counter, $mode)
	{
		/**
		 * @var $lng            ilLanguage
		 * @var $rbacreview     ilRbacReview
		 * @var $ilUser         ilObjUser
		 * @var $ilObjDataCache ilObjectDataCache
		 */
		global $lng, $rbacreview, $ilUser, $ilObjDataCache;

		$tpl->setCurrentBlock('posts_row');

		if(ilForumProperties::getInstance($ilObjDataCache->lookupObjId($_GET['ref_id']))->getMarkModeratorPosts() == 1)
		{
			if($post->getIsAuthorModerator() === null && $is_moderator = ilForum::_isModerator($_GET['ref_id'], $post->getPosAuthorId())  )
			{
				$rowCol = 'ilModeratorPosting';
			}
			else if($post->getIsAuthorModerator())
			{
				$rowCol = 'ilModeratorPosting';
			}
			else
			{
				$rowCol = ilUtil::switchColor($counter, 'tblrow1', 'tblrow2');
			}
		}
		else
		{
			$rowCol = ilUtil::switchColor($counter, 'tblrow1', 'tblrow2');
		}

		$tpl->setVariable('ROWCOL', ' ' . $rowCol);

		// post is censored
		if($post->isCensored())
		{
			// display censorship advice
			$tpl->setVariable('TXT_CENSORSHIP_ADVICE', $lng->txt('post_censored_comment_by_moderator'));
			// highlight censored posts
			$rowCol = 'tblrowmarked';
		}

		// set row color
		$tpl->setVariable('ROWCOL', ' ' . $rowCol);
		// if post is not activated display message for the owner
		if(!$post->isActivated() && $post->isOwner($ilUser->getId()))
		{
			$tpl->setVariable('POST_NOT_ACTIVATED_YET', $lng->txt('frm_post_not_activated_yet'));
		}

		$authorinfo = new ilForumAuthorInformation(
			$post->getPosAuthorId(),
			$post->getDisplayUserId(),
			$post->getUserAlias(),
			$post->getImportName()
		);

		if($authorinfo->hasSuffix())
		{
			$tpl->setVariable('AUTHOR', $authorinfo->getSuffix());
			$tpl->setVariable('USR_NAME', $post->getUserAlias());
		}
		else
		{
			$tpl->setVariable('AUTHOR', $authorinfo->getAuthorShortName());
			if($authorinfo->getAuthorName(true))
			{
				$tpl->setVariable('USR_NAME', $authorinfo->getAuthorName(true));
			}
		}

		if(self::MODE_EXPORT_CLIENT == $mode )
		{
			if($authorinfo->getAuthor()->getPref('public_profile') != 'n')
			{
				$tpl->setVariable('TXT_REGISTERED', $lng->txt('registered_since'));
				$tpl->setVariable('REGISTERED_SINCE', $this->frm->convertDate($authorinfo->getAuthor()->getCreateDate()));
			}
			
			if($post->getDisplayUserId())
			{
				if($this->is_moderator)
				{
					$num_posts = $this->frm->countUserArticles($post->getDisplayUserId());
				}
				else
				{
					$num_posts = $this->frm->countActiveUserArticles($post->getDisplayUserId());
				}
				$tpl->setVariable('TXT_NUM_POSTS', $lng->txt('forums_posts'));
				$tpl->setVariable('NUM_POSTS', $num_posts);
			}
		}

		$tpl->setVariable('USR_IMAGE', $authorinfo->getProfilePicture());
		if($authorinfo->getAuthor()->getId() && ilForum::_isModerator((int)$_GET['ref_id'], $post->getPosAuthorId()))
		{
			if($authorinfo->getAuthor()->getGender() == 'f')
			{
				$tpl->setVariable('ROLE', $lng->txt('frm_moderator_f'));
			}
			else if($authorinfo->getAuthor()->getGender() == 'm')
			{
				$tpl->setVariable('ROLE', $lng->txt('frm_moderator_m'));
			}
		}

		// get create- and update-dates
		if($post->getUpdateUserId() > 0)
		{
			$spanClass = '';

			// last update from moderator?
			$posMod = $this->frm->getModeratorFromPost($post->getId());

			if(is_array($posMod) && $posMod['top_mods'] > 0)
			{
				$MODS = $rbacreview->assignedUsers($posMod['top_mods']);
				if(is_array($MODS))
				{
					if(in_array($post->getUpdateUserId(), $MODS))
						$spanClass = 'moderator_small';
				}
			}

			$post->setChangeDate($post->getChangeDate());

			if($spanClass == '') $spanClass = 'small';

			require_once 'Modules/Forum/classes/class.ilForumAuthorInformation.php';
			$authorinfo = new ilForumAuthorInformation(
				$post->getPosAuthorId(),
				$post->getUpdateUserId(),
				'',
				''
			);

			$tpl->setVariable('POST_UPDATE_TXT', $lng->txt('edited_on') . ': ' . $this->frm->convertDate($post->getChangeDate()) . ' - ' . strtolower($lng->txt('by')));
			$tpl->setVariable('UPDATE_AUTHOR', $authorinfo->getLinkedAuthorShortName());
			if($authorinfo->getAuthorName(true))
			{
				$tpl->setVariable('UPDATE_USR_NAME', $authorinfo->getAuthorName(true));
			}
		}

		// prepare post
		$post->setMessage($this->frm->prepareText($post->getMessage()));
		$tpl->setVariable('POST_DATE', $this->frm->convertDate($post->getCreateDate()));
		$tpl->setVariable('SUBJECT', $post->getSubject());

		if(!$post->isCensored())
		{
			// post from moderator?
			$modAuthor = $this->frm->getModeratorFromPost($post->getId());

			$spanClass = "";

			if(is_array($modAuthor) && $modAuthor['top_mods'] > 0)
			{
				$MODS = $rbacreview->assignedUsers($modAuthor['top_mods']);
				if(is_array($MODS) && in_array($post->getDisplayUserId(), $MODS))
				{
					$spanClass = 'moderator';
				}
			}

			// possible bugfix for mantis #8223
			if($post->getMessage() == strip_tags($post->getMessage()))
			{
				// We can be sure, that there are not html tags
				$post->setMessage(nl2br($post->getMessage()));
			}

			if($spanClass != "")
			{
				$tpl->setVariable('POST', "<span class=\"" . $spanClass . "\">" . ilRTE::_replaceMediaObjectImageSrc($post->getMessage(), 1) . "</span>");
			}
			else
			{
				$tpl->setVariable('POST', ilRTE::_replaceMediaObjectImageSrc($post->getMessage(), 1));
			}
		}
		else
		{
			$tpl->setVariable('POST', "<span class=\"moderator\">" . nl2br($post->getCensorshipComment()) . "</span>");
		}

		$tpl->parseCurrentBlock('posts_row');
	}
}