<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Block/classes/class.ilBlockGUI.php';
require_once './Modules/Forum/classes/class.ilForum.php';

/**
 * Class ilForumPostingDraftsBlockGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ModulesForum
 * @ilCtrl_IsCalledBy ilForumPostingDraftsBlockGUI: ilColumnGUI
 */
class ilForumPostingDraftsBlockGUI extends ilBlockGUI
{
	/**
	 * @var string
	 */
	public static $block_type = 'pdfrmpostdraft';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		parent::__construct();

		$lng->loadLanguageModule('forum');

		$this->setLimit(5);
		$this->setImage(ilUtil::getImagePath('icon_frm.svg'));
		$this->setTitle($lng->txt('frm_my_posting_drafts'));
		$this->setAvailableDetailLevels(3);
		$this->allow_moving = true;
	}

	/**
	 * @return string
	 */
	public function executeCommand()
	{
		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $ilCtrl;

		$cmd = $ilCtrl->getCmd('getHTML');

		return $this->$cmd();
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function isRepositoryObject()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHTML()
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		if($this->getCurrentDetailLevel() == 0 || !$ilSetting->get('save_post_drafts', 0) || !$ilSetting->get('block_activated_pdfrmpostdraft', 0))
		{
			return '';
		}
		else
		{
			return parent::getHTML();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function fillRow($draft)
	{
		$this->tpl->setVariable('SUBJECT', $draft['subject']);			
		$this->tpl->setVariable('SOURCE', $draft['source']);			
		$this->tpl->setVariable("HREF", $draft['href']);
		if($this->getCurrentDetailLevel() > 2)
		{
			$this->tpl->setVariable('CREATE_DATE', ilDatePresentation::formatDate(new ilDateTime($draft['create_date'], IL_CAL_DATETIME)));
		}	
	}

	/**
	 * {@inheritdoc}
	 */
	public function fillDataSection()
	{
		global $ilUser;
		
		require_once './Modules/Forum/classes/class.ilForumPostDraft.php';
		require_once './Modules/Forum/classes/class.ilForumUtil.php';
		require_once './Services/Link/classes/class.ilLink.php';
		
		$drafts_instances = ilForumPostDraft::getDraftInstancesByUserId($ilUser->getId());
		
		$draft_as_array = array();
		$data = array();
		if(is_array($drafts_instances) || count($drafts_instances) > 0 )
		{
			foreach($drafts_instances as $draft)
			{
				if($draft->getThreadId() == 0)
				{
					continue 1;
				}
				
				$draft_as_array['subject']  = $draft->getPostSubject();
				$draft_as_array['draft_id'] = $draft->getDraftId();
				
				$params['thr_pk']       = $draft->getThreadId();
				$params['pos_pk']       = $draft->getPostId();
				$params['cmd']          = 'viewThread';
				$obj_id                 = ilForum::_lookupObjIdForForumId($draft->getForumId());
				$ref_id                 = end(ilObject::_getAllReferences($obj_id));
				$draft_as_array['href'] = ilLink::_getLink($ref_id, 'frm', $params) . '#draft_' . $draft->getDraftId();
				
				$information              = ilForumUtil::collectPostInformationByPostId($draft->getPostId());
				$draft_as_array['source'] = implode('/', $information);
				$draft_as_array['create_date'] = $draft->getPostDate();
				
				$data[] = $draft_as_array;
			}
		}
		$this->setData($data);

		if($this->getCurrentDetailLevel() > 1 && count($this->data) > 0)
		{
			$this->setRowTemplate('tpl.pd_frm_posting_drafts_row.html', 'Modules/Forum');
			if($this->getCurrentDetailLevel() > 2)
			{
				$this->setColSpan(2);
			}
			parent::fillDataSection();
		}
		else
		{
			$this->setEnableNumInfo(false);
			if(count($this->data) == 0)
			{
				$this->setEnableDetailRow(false);
			}
			$this->setDataSection($this->getOverview());
		}
	}

	/**
	 * Get overview.
	 */
	protected function getOverview()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$tpl = new ilTemplate('tpl.pd_frm_posting_drafts_row.html', true, true, 'Modules/Forum');
		$tpl->setCurrentBlock('overview');
		$tpl->setVariable('NUM_FRM_POSTING_DRAFTS', count($this->data));
		if(count($this->data) == 1)
		{
			$tpl->setVariable('TXT_FRM_POSTING_DRAFTS', $lng->txt('frm_posting_draft'));
		}
		else
		{
			$tpl->setVariable('TXT_FRM_POSTING_DRAFTS', $lng->txt('frm_posting_drafts'));
		}
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}
}