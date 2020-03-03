<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    
    public $ctrl;
    public $lng;
    public $access;
    public $error;
    public $user;
    public $ilObjDataCache;
    
    public function __construct()
    {
        global $DIC;
        
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->error = $DIC['ilErr'];
        
        $this->user = $DIC->user();
        $this->ilObjDataCache = $DIC['ilObjDataCache'];
        
        $forum = new ilObjForum((int) $_GET['ref_id']);
        $this->frm = $forum->Forum;
        $this->objProperties = ilForumProperties::getInstance($forum->getId());
        
        $this->frm->setForumId($forum->getId());
        $this->frm->setForumRefId($forum->getRefId());
        
        $this->lng->loadLanguageModule('forum');

        $this->is_moderator = $this->access->checkAccess('moderate_frm', '', (int) $_GET['ref_id']);
    }

    /**
     * @param int $objId
     * @param ilForumTopic $thread
     */
    public function ensureThreadBelongsToForum(int $objId, \ilForumTopic $thread)
    {
        $forumId = \ilObjForum::lookupForumIdByObjId($objId);
        if ((int) $thread->getForumId() !== (int) $forumId) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
    }

    /**
     *
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        switch ($next_class) {
            default:
                return $this->$cmd();
                break;
        }
    }

    public function printThread()
    {
        if (!$this->access->checkAccess('read,visible', '', (int) $_GET['ref_id'])) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
        
        // fau: call prepare to init mathjax rendering
        $this->prepare();

        ilDatePresentation::setUseRelativeDates(false);

        $tpl                 = new ilTemplate('tpl.forums_export_print.html', true, true, 'Modules/Forum');
        $location_stylesheet = ilUtil::getStyleSheetLocation();
        $tpl->setVariable('LOCATION_STYLESHEET', $location_stylesheet);

        iljQueryUtil::initjQuery();

        $this->frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array((int) $_GET['thr_top_fk']));
        if (is_array($frmData = $this->frm->getOneTopic())) {
            $topic = new ilForumTopic(addslashes($_GET['print_thread']), $this->is_moderator);
            $this->ensureThreadBelongsToForum((int) $this->frm->getForumId(), $topic);

            $topic->setOrderField('frm_posts_tree.rgt');
            $first_post      = $topic->getFirstPostNode();
            $post_collection = $topic->getPostTree($first_post);
            $num_posts       = count($post_collection);

            $tpl->setVariable('TITLE', $topic->getSubject());
            $tpl->setVariable(
                'HEADLINE',
                $this->lng->txt('forum') . ': ' . $frmData['top_name'] . ' > ' .
                $this->lng->txt('forums_thread') . ': ' . $topic->getSubject() . ' > ' .
                $this->lng->txt('forums_count_art') . ': ' . $num_posts
            );

            $z = 0;
            foreach ($post_collection as $post) {
                $this->renderPostHtml($tpl, $post, $z++, self::MODE_EXPORT_WEB);
            }
        }
        $tpl->show();
    }
    
    public function printPost()
    {
        if (!$this->access->checkAccess('read,visible', '', $_GET['ref_id'])) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        // call prepare to init mathjax rendering
        $this->prepare();

        ilDatePresentation::setUseRelativeDates(false);

        $tpl                 = new ilTemplate('tpl.forums_export_print.html', true, true, 'Modules/Forum');
        $location_stylesheet = ilUtil::getStyleSheetLocation();
        $tpl->setVariable('LOCATION_STYLESHEET', $location_stylesheet);

        iljQueryUtil::initjQuery();

        $this->frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array((int) $_GET['top_pk']));
        if (is_array($frmData = $this->frm->getOneTopic())) {
            $post = new ilForumPost((int) $_GET['print_post'], $this->is_moderator);
            $this->ensureThreadBelongsToForum((int) $this->frm->getForumId(), $post->getThread());

            $tpl->setVariable('TITLE', $post->getThread()->getSubject());
            $tpl->setVariable('HEADLINE', $this->lng->txt('forum') . ': ' . $frmData['top_name'] . ' > ' . $this->lng->txt('forums_thread') . ': ' . $post->getThread()->getSubject());

            $this->renderPostHtml($tpl, $post, 0, self::MODE_EXPORT_WEB);
        }
        $tpl->show();
    }

    /**
     *
     */
    public function exportHTML()
    {
        if (!$this->access->checkAccess('read,visible', '', $_GET['ref_id'])) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        // call prepare to init mathjax rendering
        $this->prepare();

        ilDatePresentation::setUseRelativeDates(false);

        $tpl = new ilTemplate('tpl.forums_export_html.html', true, true, 'Modules/Forum');
        $location_stylesheet = ilUtil::getStyleSheetLocation();
        $tpl->setVariable('LOCATION_STYLESHEET', $location_stylesheet);
        $tpl->setVariable('BASE', (substr(ILIAS_HTTP_PATH, -1) == '/' ? ILIAS_HTTP_PATH : ILIAS_HTTP_PATH . '/'));

        $threads = [];
        $isModerator = $this->is_moderator;
        $postIds = (array) $_POST['thread_ids'];
        array_walk($postIds, function ($threadId) use (&$threads, $isModerator) {
            $thread = new \ilForumTopic($threadId, $isModerator);
            $this->ensureThreadBelongsToForum((int) $this->frm->getForumId(), $thread);

            $threads[] = $thread;
        });

        $j = 0;
        foreach ($threads as $topic) {
            $this->frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array($topic->getForumId()));
            if (is_array($thread_data = $this->frm->getOneTopic())) {
                if (0 == $j) {
                    $tpl->setVariable('TITLE', $thread_data['top_name']);
                }

                $first_post = $topic->getFirstPostNode();
                $topic->setOrderField('frm_posts_tree.rgt');
                $post_collection = $topic->getPostTree($first_post);

                $z = 0;
                foreach ($post_collection as $post) {
                    $this->renderPostHtml($tpl, $post, $z++, self::MODE_EXPORT_CLIENT);
                }

                $tpl->setCurrentBlock('thread_headline');
                $tpl->setVariable('T_TITLE', $topic->getSubject());
                if ($this->is_moderator) {
                    $tpl->setVariable('T_NUM_POSTS', $topic->countPosts(true));
                } else {
                    $tpl->setVariable('T_NUM_POSTS', $topic->countActivePosts(true));
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
                $tpl->setVariable('T_TXT_FORUM', $this->lng->txt('forum') . ': ');
                $tpl->setVariable('T_TXT_TOPIC', $this->lng->txt('forums_thread') . ': ');
                $tpl->setVariable('T_TXT_AUTHOR', $this->lng->txt('forums_thread_create_from') . ': ');
                $tpl->setVariable('T_TXT_NUM_POSTS', $this->lng->txt('forums_articles') . ': ');
                $tpl->setVariable('T_TXT_NUM_VISITS', $this->lng->txt('visits') . ': ');
                $tpl->parseCurrentBlock();

                ++$j;
            }
            
            $tpl->setCurrentBlock('thread_block');
            $tpl->parseCurrentBlock();
        }

        ilUtil::deliverData($tpl->get('DEFAULT', false, false, false, true, false, false), 'forum_html_export_' . $_GET['ref_id'] . '.html');
    }

    /**
     * @param \ilTemplate $tpl
     * @param ilForumPost $post
     * @param int $counter
     * @param int $mode
     */
    protected function renderPostHtml(\ilTemplate $tpl, ilForumPost $post, $counter, $mode)
    {
        $tpl->setCurrentBlock('posts_row');

        if (ilForumProperties::getInstance($this->ilObjDataCache->lookupObjId($_GET['ref_id']))->getMarkModeratorPosts() == 1) {
            if ($post->getIsAuthorModerator() === null && $is_moderator = ilForum::_isModerator($_GET['ref_id'], $post->getPosAuthorId())) {
                $rowCol = 'ilModeratorPosting';
            } elseif ($post->getIsAuthorModerator()) {
                $rowCol = 'ilModeratorPosting';
            } else {
                $rowCol = ilUtil::switchColor($counter, 'tblrow1', 'tblrow2');
            }
        } else {
            $rowCol = ilUtil::switchColor($counter, 'tblrow1', 'tblrow2');
        }

        $tpl->setVariable('ROWCOL', ' ' . $rowCol);

        if ($post->isCensored()) {
            $tpl->setVariable('TXT_CENSORSHIP_ADVICE', $this->lng->txt('post_censored_comment_by_moderator'));
            $rowCol = 'tblrowmarked';
        }

        $tpl->setVariable('ROWCOL', ' ' . $rowCol);
        if (!$post->isActivated() && $post->isOwner($this->user->getId())) {
            $tpl->setVariable('POST_NOT_ACTIVATED_YET', $this->lng->txt('frm_post_not_activated_yet'));
        }

        $authorinfo = new ilForumAuthorInformation(
            $post->getPosAuthorId(),
            $post->getDisplayUserId(),
            $post->getUserAlias(),
            $post->getImportName()
        );

        if ($authorinfo->hasSuffix()) {
            $tpl->setVariable('AUTHOR', $authorinfo->getSuffix());
            $tpl->setVariable('USR_NAME', $post->getUserAlias());
        } else {
            $tpl->setVariable('AUTHOR', $authorinfo->getAuthorShortName());
            if ($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized()) {
                $tpl->setVariable('USR_NAME', $authorinfo->getAuthorName(true));
            }
        }

        if (self::MODE_EXPORT_CLIENT == $mode) {
            if ($authorinfo->getAuthor()->getPref('public_profile') != 'n') {
                $tpl->setVariable('TXT_REGISTERED', $this->lng->txt('registered_since'));
                $tpl->setVariable('REGISTERED_SINCE', $this->frm->convertDate($authorinfo->getAuthor()->getCreateDate()));
            }
            
            if ($post->getDisplayUserId()) {
                if ($this->is_moderator) {
                    $num_posts = $this->frm->countUserArticles($post->getDisplayUserId());
                } else {
                    $num_posts = $this->frm->countActiveUserArticles($post->getDisplayUserId());
                }
                $tpl->setVariable('TXT_NUM_POSTS', $this->lng->txt('forums_posts'));
                $tpl->setVariable('NUM_POSTS', $num_posts);
            }
        }

        $tpl->setVariable('USR_IMAGE', $authorinfo->getProfilePicture());
        if ($authorinfo->getAuthor()->getId() && ilForum::_isModerator((int) $_GET['ref_id'], $post->getPosAuthorId())) {
            if ($authorinfo->getAuthor()->getGender() == 'f') {
                $tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_f'));
            } elseif ($authorinfo->getAuthor()->getGender() == 'm') {
                $tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_m'));
            } elseif ($authorinfo->getAuthor()->getGender() == 'n') {
                $tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_n'));
            }
        }

        // get create- and update-dates
        if ($post->getUpdateUserId() > 0) {
            $spanClass = '';
            if (ilForum::_isModerator((int) $_GET['ref_id'], $post->getUpdateUserId())) {
                $spanClass = 'moderator_small';
            }

            $post->setChangeDate($post->getChangeDate());

            $authorinfo = new ilForumAuthorInformation(
                $post->getPosAuthorId(),
                $post->getDisplayUserId(),
                $post->getUserAlias(),
                ''
            );

            $tpl->setVariable('POST_UPDATE_TXT', $this->lng->txt('edited_on') . ': ' . $this->frm->convertDate($post->getChangeDate()) . ' - ' . strtolower($this->lng->txt('by')));
            $tpl->setVariable('UPDATE_AUTHOR', $authorinfo->getLinkedAuthorShortName());
            if ($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized()) {
                $tpl->setVariable('UPDATE_USR_NAME', $authorinfo->getAuthorName(true));
            }
        }

        // prepare post
        $post->setMessage($this->frm->prepareText($post->getMessage()));
        $tpl->setVariable('POST_DATE', $this->frm->convertDate($post->getCreateDate()));
        $tpl->setVariable('SUBJECT', $post->getSubject());

        if (!$post->isCensored()) {
            $spanClass = "";
            if (ilForum::_isModerator((int) $_GET['ref_id'], $post->getDisplayUserId())) {
                $spanClass = 'moderator';
            }

            // possible bugfix for mantis #8223
            if ($post->getMessage() == strip_tags($post->getMessage())) {
                // We can be sure, that there are not html tags
                $post->setMessage(nl2br($post->getMessage()));
            }

            if ($spanClass != "") {
                $tpl->setVariable('POST', "<span class=\"" . $spanClass . "\">" . ilRTE::_replaceMediaObjectImageSrc($post->getMessage(), 1) . "</span>");
            } else {
                $tpl->setVariable('POST', ilRTE::_replaceMediaObjectImageSrc($post->getMessage(), 1));
            }
        } else {
            $tpl->setVariable('POST', "<span class=\"moderator\">" . nl2br($post->getCensorshipComment()) . "</span>");
        }

        $tpl->parseCurrentBlock('posts_row');
    }

    /**
     * Prepare the export (init MathJax rendering)
     */
    protected function prepare()
    {
        ilMathJax::getInstance()
            ->init(ilMathJax::PURPOSE_EXPORT)
            ->setZoomFactor(10);
    }
}
