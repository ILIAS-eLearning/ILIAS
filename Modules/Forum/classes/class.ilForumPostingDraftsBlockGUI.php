<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

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
     * @var ilSetting
     */
    private $settings;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        parent::__construct();
        
        $this->settings = $DIC->settings();
        
        $this->lng->loadLanguageModule('forum');

        $this->setLimit(5);
        $this->setImage(ilUtil::getImagePath('icon_frm.svg'));
        $this->setTitle($this->lng->txt('frm_my_posting_drafts'));
        $this->setAvailableDetailLevels(3);
        $this->allow_moving = true;
    }

    /**
     * @return string
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd('getHTML');
        return $this->$cmd();
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getHTML()
    {
        if ($this->getCurrentDetailLevel() == 0 || !$this->settings->get('save_post_drafts', 0) || !$this->settings->get('block_activated_pdfrmpostdraft', 0)) {
            return '';
        } else {
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
        if ($this->getCurrentDetailLevel() > 2) {
            $this->tpl->setVariable('CREATE_DATE', ilDatePresentation::formatDate(new ilDateTime($draft['create_date'], IL_CAL_DATETIME)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fillDataSection()
    {
        $drafts_instances = ilForumPostDraft::getDraftInstancesByUserId($this->user->getId());
        
        $draft_as_array = array();
        $data = array();
        if (is_array($drafts_instances) && count($drafts_instances) > 0) {
            foreach ($drafts_instances as $draft) {
                $is_thread = false;
                if ((int) $draft->getThreadId() == 0) {
                    $is_thread = true;
                }
                
                $draft_as_array['subject']  = $draft->getPostSubject();
                $draft_as_array['draft_id'] = $draft->getDraftId();
                
                $information              = ilForumUtil::collectPostInformationByPostId($draft->getPostId());
                $draft_as_array['source'] = implode('/', $information);
                $draft_as_array['create_date'] = $draft->getPostDate();
                
                $obj_id                 = ilForum::_lookupObjIdForForumId($draft->getForumId());
                $ref_id                 = end(ilObject::_getAllReferences($obj_id));
                
                if ($is_thread) {
                    $params['cmd']          = 'editThreadDraft';
                    $params['draft_id']     = $draft->getDraftId();
                    $draft_as_array['href'] = ilLink::_getLink($ref_id, 'frm', $params);
                } else {
                    $params['thr_pk']       = $draft->getThreadId();
                    $params['pos_pk']       = $draft->getPostId();
                    $params['cmd']          = 'viewThread';
                    $draft_as_array['href'] = ilLink::_getLink($ref_id, 'frm', $params) . '#draft_' . $draft->getDraftId();
                }
                
                $data[] = $draft_as_array;
            }
        }
        $this->setData($data);

        if ($this->getCurrentDetailLevel() > 1 && count($this->data) > 0) {
            $this->setRowTemplate('tpl.pd_frm_posting_drafts_row.html', 'Modules/Forum');
            if ($this->getCurrentDetailLevel() > 2) {
                $this->setColSpan(2);
            }
            parent::fillDataSection();
        } else {
            $this->setEnableNumInfo(false);
            if (count($this->data) == 0) {
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
        $tpl = new ilTemplate('tpl.pd_frm_posting_drafts_row.html', true, true, 'Modules/Forum');
        $tpl->setCurrentBlock('overview');
        $tpl->setVariable('NUM_FRM_POSTING_DRAFTS', count($this->data));
        if (count($this->data) == 1) {
            $tpl->setVariable('TXT_FRM_POSTING_DRAFTS', $this->lng->txt('frm_posting_draft'));
        } else {
            $tpl->setVariable('TXT_FRM_POSTING_DRAFTS', $this->lng->txt('frm_posting_drafts'));
        }
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }
}
