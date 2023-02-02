<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Forum export to HTML and Print.
 * @author Wolfgang Merkens <wmerkens@databay.de>
 * @ingroup ModulesForum
 */
class ilForumExportGUI
{
    private const MODE_EXPORT_WEB = 1;
    private const MODE_EXPORT_CLIENT = 2;

    public ilCtrlInterface $ctrl;
    public ilLanguage $lng;
    public ilAccessHandler $access;
    public ilErrorHandling $error;
    public ilObjUser $user;
    public ilObjectDataCache $ilObjDataCache;
    protected bool $is_moderator = false;
    protected ilForum $frm;
    private ilForumProperties $objProperties;
    private GlobalHttpState $http;
    private Refinery $refinery;
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
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->ref_id = $this->retrieveRefId();

        $forum = new ilObjForum($this->ref_id);
        $this->frm = $forum->Forum;
        $this->objProperties = ilForumProperties::getInstance($forum->getId());

        $this->frm->setForumId($forum->getId());
        $this->frm->setForumRefId($forum->getRefId());

        $this->lng->loadLanguageModule('forum');

        $this->is_moderator = $this->access->checkAccess('moderate_frm', '', $this->ref_id);
    }

    private function retrieveRefId(): int
    {
        $ref_id = 0;
        if ($this->http->wrapper()->query()->has('ref_id')) {
            $ref_id = $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        return $ref_id;
    }

    private function prepare(): void
    {
        ilMathJax::getInstance()
            ->init(ilMathJax::PURPOSE_EXPORT)
            ->setZoomFactor(10);
    }

    private function ensureThreadBelongsToForum(int $objId, ilForumTopic $thread): void
    {
        $forumId = ilObjForum::lookupForumIdByObjId($objId);
        if ($thread->getForumId() !== $forumId) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        $this->$cmd();
    }

    protected function renderPostHtml(ilGlobalTemplateInterface $tpl, ilForumPost $post, int $counter, int $mode): void
    {
        $tpl->setCurrentBlock('posts_row');

        if (ilForumProperties::getInstance($this->ilObjDataCache->lookupObjId($this->ref_id))->getMarkModeratorPosts()) {
            if ($post->isAuthorModerator() === null && $is_moderator = ilForum::_isModerator(
                $this->ref_id,
                $post->getPosAuthorId()
            )) {
                $rowCol = 'ilModeratorPosting';
            } elseif ($post->isAuthorModerator()) {
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
            (string) $post->getUserAlias(),
            (string) $post->getImportName()
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

        if (self::MODE_EXPORT_CLIENT === $mode) {
            if ($authorinfo->getAuthor()->getPref('public_profile') !== 'n') {
                $tpl->setVariable('TXT_REGISTERED', $this->lng->txt('registered_since'));
                $tpl->setVariable(
                    'REGISTERED_SINCE',
                    $this->frm->convertDate($authorinfo->getAuthor()->getCreateDate())
                );
            }

            if ($post->getDisplayUserId() !== 0) {
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
            $this->ref_id,
            $post->getPosAuthorId()
        )) {
            if ($authorinfo->getAuthor()->getGender() === 'f') {
                $tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_f'));
            } elseif ($authorinfo->getAuthor()->getGender() === 'm') {
                $tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_m'));
            } else {
                $tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_n'));
            }
        }

        if ($post->getUpdateUserId() > 0) {
            $spanClass = '';
            if (ilForum::_isModerator($this->ref_id, $post->getUpdateUserId())) {
                $spanClass = 'moderator_small';
            }

            $post->setChangeDate($post->getChangeDate());

            $authorinfo = new ilForumAuthorInformation(
                $post->getPosAuthorId(),
                $post->getDisplayUserId(),
                (string) $post->getUserAlias(),
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

        $post->setMessage($this->frm->prepareText($post->getMessage()));
        $tpl->setVariable('POST_DATE', $this->frm->convertDate($post->getCreateDate()));
        $tpl->setVariable('SUBJECT', $post->getSubject());

        if (!$post->isCensored()) {
            $spanClass = "";
            if (ilForum::_isModerator($this->ref_id, $post->getDisplayUserId())) {
                $spanClass = 'moderator';
            }

            // possible bugfix for mantis #8223
            if ($post->getMessage() === strip_tags($post->getMessage())) {
                // We can be sure, that there are not html tags
                $post->setMessage(nl2br($post->getMessage()));
            }

            if ($spanClass !== '') {
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
            $tpl->setVariable('POST', "<span class=\"moderator\">" . nl2br((string) $post->getCensorshipComment()) . "</span>");
        }

        $tpl->parseCurrentBlock('posts_row');
    }

    public function printThread(): void
    {
        if (
            !$this->access->checkAccess('read,visible', '', $this->ref_id) ||
            !$this->http->wrapper()->query()->has('thr_top_fk') ||
            !$this->http->wrapper()->query()->has('print_thread')
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->prepare();

        ilDatePresentation::setUseRelativeDates(false);

        $tpl = new ilGlobalTemplate('tpl.forums_export_print.html', true, true, 'Modules/Forum');
        $location_stylesheet = ilUtil::getStyleSheetLocation();
        $tpl->setVariable('LOCATION_STYLESHEET', $location_stylesheet);

        iljQueryUtil::initjQuery($tpl);
        ilMathJax::getInstance()->includeMathJax($tpl);

        $this->frm->setMDB2WhereCondition('top_pk = %s ', ['integer'], [$this->http->wrapper()->query()->retrieve(
            'thr_top_fk',
            $this->refinery->kindlyTo()->int()
        )]);
        $frmData = $this->frm->getOneTopic();

        if ($frmData->getTopPk() > 0) {
            $topic = new ilForumTopic($this->http->wrapper()->query()->retrieve(
                'print_thread',
                $this->refinery->kindlyTo()->int()
            ), $this->is_moderator);

            $this->ensureThreadBelongsToForum($this->frm->getForumId(), $topic);

            $topic->setOrderField('frm_posts_tree.rgt');
            $first_post = $topic->getPostRootNode();
            $post_collection = $topic->getPostTree($first_post);
            $num_posts = count($post_collection);

            $tpl->setVariable('TITLE', $topic->getSubject());
            $tpl->setVariable(
                'HEADLINE',
                $this->lng->txt('forum') . ': ' . $frmData->getTopName() . ' > ' .
                $this->lng->txt('forums_thread') . ': ' . $topic->getSubject() . ' > ' .
                $this->lng->txt('forums_count_art') . ': ' . $num_posts
            );

            $i = 0;
            foreach ($post_collection as $post) {
                $this->renderPostHtml($tpl, $post, $i++, self::MODE_EXPORT_WEB);
            }
        }

        $tpl->printToStdout();
    }

    public function printPost(): void
    {
        if (!$this->access->checkAccess('read,visible', '', $this->ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->prepare();

        ilDatePresentation::setUseRelativeDates(false);

        $tpl = new ilGlobalTemplate('tpl.forums_export_print.html', true, true, 'Modules/Forum');
        $location_stylesheet = ilUtil::getStyleSheetLocation();
        $tpl->setVariable('LOCATION_STYLESHEET', $location_stylesheet);

        iljQueryUtil::initjQuery($tpl);
        ilMathJax::getInstance()->includeMathJax($tpl);

        $this->frm->setMDB2WhereCondition('top_pk = %s ', ['integer'], [$this->http->wrapper()->query()->retrieve(
            'top_pk',
            $this->refinery->kindlyTo()->int()
        )]);
        $frmData = $this->frm->getOneTopic();

        if ($frmData->getTopPk() > 0) {
            $post = new ilForumPost($this->http->wrapper()->query()->retrieve(
                'print_post',
                $this->refinery->kindlyTo()->int()
            ), $this->is_moderator);
            $this->ensureThreadBelongsToForum($this->frm->getForumId(), $post->getThread());

            $tpl->setVariable('TITLE', $post->getThread()->getSubject());
            $tpl->setVariable(
                'HEADLINE',
                $this->lng->txt('forum') . ': ' . $frmData->getTopName() . ' > ' .
                    $this->lng->txt('forums_thread') . ': ' . $post->getThread()->getSubject()
            );

            $this->renderPostHtml($tpl, $post, 0, self::MODE_EXPORT_WEB);
        }
        $tpl->printToStdout();
    }

    public function exportHTML(): void
    {
        if (!$this->access->checkAccess('read,visible', '', $this->ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->prepare();

        ilDatePresentation::setUseRelativeDates(false);

        $tpl = new ilGlobalTemplate('tpl.forums_export_html.html', true, true, 'Modules/Forum');
        $location_stylesheet = ilUtil::getStyleSheetLocation();
        $tpl->setVariable('LOCATION_STYLESHEET', $location_stylesheet);
        $tpl->setVariable('BASE', (str_ends_with(ILIAS_HTTP_PATH, '/') ? ILIAS_HTTP_PATH : ILIAS_HTTP_PATH . '/'));

        iljQueryUtil::initjQuery($tpl);
        ilMathJax::getInstance()->includeMathJax($tpl);

        /** @var ilForumTopic[] $threads */
        $threads = [];
        $thread_ids = [];
        $isModerator = $this->is_moderator;
        if ($this->http->wrapper()->post()->has('thread_ids')) {
            $thread_ids = $this->http->wrapper()->post()->retrieve(
                'thread_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }
        array_walk($thread_ids, function (int $threadId) use (&$threads, $isModerator): void {
            $thread = new ilForumTopic($threadId, $isModerator);
            $this->ensureThreadBelongsToForum($this->frm->getForumId(), $thread);

            $threads[] = $thread;
        });

        $i = 0;
        foreach ($threads as $topic) {
            $this->frm->setMDB2WhereCondition('top_pk = %s ', ['integer'], [$topic->getForumId()]);
            $frmData = $this->frm->getOneTopic();

            if ($frmData->getTopPk() > 0) {
                if (0 === $i) {
                    $tpl->setVariable('TITLE', $frmData->getTopName());
                }

                $first_post = $topic->getPostRootNode();
                $topic->setOrderField('frm_posts_tree.rgt');
                $post_collection = $topic->getPostTree($first_post);

                $j = 0;
                foreach ($post_collection as $post) {
                    $this->renderPostHtml($tpl, $post, $j++, self::MODE_EXPORT_CLIENT);
                }

                $tpl->setCurrentBlock('thread_headline');
                $tpl->setVariable('T_TITLE', $topic->getSubject());
                if ($this->is_moderator) {
                    $tpl->setVariable('T_NUM_POSTS', $topic->countPosts(true));
                } else {
                    $tpl->setVariable('T_NUM_POSTS', $topic->countActivePosts(true));
                }
                $tpl->setVariable('T_NUM_VISITS', $topic->getVisits());
                $tpl->setVariable('T_FORUM', $frmData->getTopName());
                $authorinfo = new ilForumAuthorInformation(
                    $topic->getThrAuthorId(),
                    $topic->getDisplayUserId(),
                    (string) $topic->getUserAlias(),
                    (string) $topic->getImportName()
                );
                $tpl->setVariable('T_AUTHOR', $authorinfo->getAuthorName());
                $tpl->setVariable('T_TXT_FORUM', $this->lng->txt('forum') . ': ');
                $tpl->setVariable('T_TXT_TOPIC', $this->lng->txt('forums_thread') . ': ');
                $tpl->setVariable('T_TXT_AUTHOR', $this->lng->txt('forums_thread_create_from') . ': ');
                $tpl->setVariable('T_TXT_NUM_POSTS', $this->lng->txt('forums_articles') . ': ');
                $tpl->setVariable('T_TXT_NUM_VISITS', $this->lng->txt('visits') . ': ');
                $tpl->parseCurrentBlock();

                ++$i;
            }

            $tpl->setCurrentBlock('thread_block');
            $tpl->parseCurrentBlock();
        }

        ilUtil::deliverData(
            $tpl->get(),
            'forum_html_export_' . $this->ref_id . '.html'
        );
    }
}
