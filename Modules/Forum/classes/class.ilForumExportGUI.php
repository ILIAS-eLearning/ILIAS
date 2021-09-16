<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Forum export to HTML and Print.
 * @author  Wolfgang Merkens <wmerkens@databay.de>
 * @version $Id$
 * @ingroup ModulesForum
 */
class ilForumExportGUI
{
    const MODE_EXPORT_WEB = 1;
    const MODE_EXPORT_CLIENT = 2;
    public $ctrl;
    public $lng;
    public $access;
    public $error;
    public $user;
    public $ilObjDataCache;
    protected bool $is_moderator = false;
    protected ilForum $frm;
    private ilForumProperties $objProperties;
    private $http_wrapper;
    private $refinery;
    private int $ref_id;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->error = $DIC['ilErr'];

        $this->user = $DIC->user();
        $this->ilObjDataCache = $DIC['ilObjDataCache'];
        $this->http_wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();

        $this->ref_id = 0;
        $this->retrieveRefId();

        $forum = new ilObjForum($this->ref_id);
        $this->frm = $forum->Forum;
        $this->objProperties = ilForumProperties::getInstance($forum->getId());

        $this->frm->setForumId($forum->getId());
        $this->frm->setForumRefId($forum->getRefId());

        $this->lng->loadLanguageModule('forum');

        $this->is_moderator = $this->access->checkAccess('moderate_frm', '', $this->retrieveRefId());
    }

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

    public function printThread() : void
    {
        if (!$this->access->checkAccess('read,visible', '', $this->retrieveRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        // fau: call prepare to init mathjax rendering
        $this->prepare();

        ilDatePresentation::setUseRelativeDates(false);

        $tpl = new ilGlobalTemplate('tpl.forums_export_print.html', true, true, 'Modules/Forum');
        $location_stylesheet = ilUtil::getStyleSheetLocation();
        $tpl->setVariable('LOCATION_STYLESHEET', $location_stylesheet);

        iljQueryUtil::initjQuery($tpl);
        ilMathJax::getInstance()->includeMathJax($tpl);
        $thr_top_fk = 0;
        if ($this->http_wrapper->query()->has('thr_top_fk')) {
            $thr_top_fk = $this->http_wrapper->query()->retrieve(
                'thr_top_fk',
                $this->refinery->kindlyTo()->int()
            );
        }

        $this->frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array((int) $thr_top_fk));
        if (is_array($frmData = $this->frm->getOneTopic())) {
            $print_thread = 0;
            if ($this->http_wrapper->query()->has('print_thread')) {
                $print_thread = $this->http_wrapper->query()->retrieve(
                    'print_thread',
                    $this->refinery->kindlyTo()->int()
                );
            }
            $topic = new ilForumTopic($print_thread, $this->is_moderator);
            $this->ensureThreadBelongsToForum($this->frm->getForumId(), $topic);

            $topic->setOrderField('frm_posts_tree.rgt');
            $first_post = $topic->getFirstPostNode();
            $post_collection = $topic->getPostTree($first_post);
            $num_posts = count($post_collection);

            $tpl->setVariable('TITLE', $topic->getSubject());
            $tpl->setVariable(
                'HEADLINE',
                $this->lng->txt('forum') . ': ' . $frmData->getTopName() . ' > ' .
                $this->lng->txt('forums_thread') . ': ' . $topic->getSubject() . ' > ' .
                $this->lng->txt('forums_count_art') . ': ' . $num_posts
            );

            $z = 0;
            foreach ($post_collection as $post) {
                $this->renderPostHtml($tpl, $post, $z++, self::MODE_EXPORT_WEB);
            }
        }
        $tpl->printToStdout();
    }

    /**
     * Prepare the export (init MathJax rendering)
     */
    protected function prepare() : void
    {
        ilMathJax::getInstance()
                 ->init(ilMathJax::PURPOSE_EXPORT)
                 ->setZoomFactor(10);
    }

    public function ensureThreadBelongsToForum(int $objId, \ilForumTopic $thread) : void
    {
        $forumId = \ilObjForum::lookupForumIdByObjId($objId);
        if ($thread->getForumId() !== $forumId) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
    }

    protected function renderPostHtml(\ilGlobalTemplate $tpl, ilForumPost $post, int $counter, int $mode) : void
    {
        $tpl->setCurrentBlock('posts_row');

        if (ilForumProperties::getInstance($this->ilObjDataCache->lookupObjId($this->retrieveRefId()))->getMarkModeratorPosts() == 1) {
            if ($post->getIsAuthorModerator() === null && $is_moderator = ilForum::_isModerator(
                    $this->retrieveRefId(),
                    $post->getPosAuthorId()
                )) {
                $rowCol = 'ilModeratorPosting';
            } else {
                if ($post->getIsAuthorModerator()) {
                    $rowCol = 'ilModeratorPosting';
                } else {
                    $rowCol = ilUtil::switchColor($counter, 'tblrow1', 'tblrow2');
                }
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
            if (!$authorinfo->isDeleted()) {
                $tpl->setVariable('USR_NAME', $authorinfo->getAlias());
            }
            $tpl->setVariable('AUTHOR', $authorinfo->getSuffix());
        } else {
            if ($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized()) {
                $tpl->setVariable('USR_NAME', $authorinfo->getAuthorName(true));
            }
            $tpl->setVariable('AUTHOR', $authorinfo->getAuthorShortName());
        }

        if (self::MODE_EXPORT_CLIENT == $mode) {
            if ($authorinfo->getAuthor()->getPref('public_profile') != 'n') {
                $tpl->setVariable('TXT_REGISTERED', $this->lng->txt('registered_since'));
                $tpl->setVariable(
                    'REGISTERED_SINCE',
                    $this->frm->convertDate($authorinfo->getAuthor()->getCreateDate())
                );
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
        if ($authorinfo->getAuthor()->getId() && ilForum::_isModerator(
                $this->retrieveRefId(),
                $post->getPosAuthorId()
            )) {
            if ($authorinfo->getAuthor()->getGender() == 'f') {
                $tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_f'));
            } else {
                if ($authorinfo->getAuthor()->getGender() == 'm') {
                    $tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_m'));
                } else {
                    if ($authorinfo->getAuthor()->getGender() == 'n') {
                        $tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_n'));
                    }
                }
            }
        }

        // get create- and update-dates
        if ($post->getUpdateUserId() > 0) {
            $spanClass = '';
            if (ilForum::_isModerator($this->retrieveRefId(), $post->getUpdateUserId())) {
                $spanClass = 'moderator_small';
            }

            $post->setChangeDate($post->getChangeDate());

            $authorinfo = new ilForumAuthorInformation(
                $post->getPosAuthorId(),
                $post->getDisplayUserId(),
                $post->getUserAlias(),
                ''
            );

            $tpl->setVariable(
                'POST_UPDATE_TXT',
                $this->lng->txt('edited_on') . ': ' . $this->frm->convertDate($post->getChangeDate()) . ' - ' . strtolower($this->lng->txt('by'))
            );
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
            if (ilForum::_isModerator($this->retrieveRefId(), $post->getDisplayUserId())) {
                $spanClass = 'moderator';
            }

            // possible bugfix for mantis #8223
            if ($post->getMessage() == strip_tags($post->getMessage())) {
                // We can be sure, that there are not html tags
                $post->setMessage(nl2br($post->getMessage()));
            }

            if ($spanClass != "") {
                $tpl->setVariable(
                    'POST',
                    "<span class=\"" . $spanClass . "\">" . ilRTE::_replaceMediaObjectImageSrc(
                        $post->getMessage(),
                        1
                    ) . "</span>"
                );
            } else {
                $tpl->setVariable('POST', ilRTE::_replaceMediaObjectImageSrc($post->getMessage(), 1));
            }
        } else {
            $tpl->setVariable('POST', "<span class=\"moderator\">" . nl2br($post->getCensorshipComment()) . "</span>");
        }

        $tpl->parseCurrentBlock('posts_row');
    }

    public function printPost() : void
    {
        if (!$this->access->checkAccess('read,visible', '', $this->retrieveRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        // call prepare to init mathjax rendering
        $this->prepare();

        ilDatePresentation::setUseRelativeDates(false);

        $tpl = new ilGlobalTemplate('tpl.forums_export_print.html', true, true, 'Modules/Forum');
        $location_stylesheet = ilUtil::getStyleSheetLocation();
        $tpl->setVariable('LOCATION_STYLESHEET', $location_stylesheet);

        iljQueryUtil::initjQuery($tpl);
        ilMathJax::getInstance()->includeMathJax($tpl);

        $top_pk = 0;
        if ($this->http_wrapper->query()->has('top_pk')) {
            $top_pk = $this->http_wrapper->query()->retrieve(
                'top_pk',
                $this->refinery->kindlyTo()->int()
            );
        }

        $this->frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array((int) $top_pk));
        if (is_array($frmData = $this->frm->getOneTopic())) {
            $print_post = 0;
            if ($this->http_wrapper->query()->has('print_post')) {
                $print_post = $this->http_wrapper->query()->retrieve(
                    'print_post',
                    $this->refinery->kindlyTo()->int()
                );
            }

            $post = new ilForumPost((int) $print_post, $this->is_moderator);
            $this->ensureThreadBelongsToForum($this->frm->getForumId(), $post->getThread());

            $tpl->setVariable('TITLE', $post->getThread()->getSubject());
            $tpl->setVariable(
                'HEADLINE',
                $this->lng->txt('forum') . ': ' . $frmData->getTopName() . ' > ' . $this->lng->txt('forums_thread') . ': ' . $post->getThread()->getSubject()
            );

            $this->renderPostHtml($tpl, $post, 0, self::MODE_EXPORT_WEB);
        }
        $tpl->printToStdout();
    }

    public function exportHTML() : void
    {
        if (!$this->access->checkAccess('read,visible', '', $this->retrieveRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        // call prepare to init mathjax rendering
        $this->prepare();

        ilDatePresentation::setUseRelativeDates(false);

        $tpl = new ilGlobalTemplate('tpl.forums_export_html.html', true, true, 'Modules/Forum');
        $location_stylesheet = ilUtil::getStyleSheetLocation();
        $tpl->setVariable('LOCATION_STYLESHEET', $location_stylesheet);
        $tpl->setVariable('BASE', (substr(ILIAS_HTTP_PATH, -1) == '/' ? ILIAS_HTTP_PATH : ILIAS_HTTP_PATH . '/'));

        iljQueryUtil::initjQuery($tpl);
        ilMathJax::getInstance()->includeMathJax($tpl);

        $threads = [];
        $isModerator = $this->is_moderator;
        $thread_ids = [];
        if ($this->http_wrapper->post()->has('thread_ids')) {
            $thread_ids = $this->http_wrapper->post()->retrieve(
                'thread_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }
        $postIds = (array) $thread_ids;
        array_walk($postIds, function ($threadId) use (&$threads, $isModerator) {
            $thread = new \ilForumTopic($threadId, $isModerator);
            $this->ensureThreadBelongsToForum($this->frm->getForumId(), $thread);

            $threads[] = $thread;
        });

        $j = 0;
        foreach ($threads as $topic) {
            $this->frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array($topic->getForumId()));
            if (is_array($thread_data = $this->frm->getOneTopic())) {
                if (0 == $j) {
                    $tpl->setVariable('TITLE', $thread_data->getTopName());
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
                $tpl->setVariable('T_FORUM', $thread_data->getTopName());
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

        ilUtil::deliverData(
            $tpl->get(),
            'forum_html_export_' . $this->retrieveRefId() . '.html'
        );
    }

    private function retrieveRefId() : int
    {
        if ($this->ref_id == 0 || $this->http_wrapper->query()->has('ref_id')) {
            $this->ref_id = $this->http_wrapper->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return $this->ref_id;
    }
}
