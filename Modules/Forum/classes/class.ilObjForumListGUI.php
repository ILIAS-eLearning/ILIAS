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

/**
 * Class ilObjForumListGUI
 * @author  Alex Killing <alex.killing@gmx.de>
 * $Id$
 * @ingroup ModulesForum
 */
class ilObjForumListGUI extends ilObjectListGUI
{
    private int $child_id;

    public function init(): void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = 'frm';
        $this->gui_class_name = ilObjForumGUI::class;

        $this->commands = ilObjForumAccess::_getCommands();
    }

    public function setChildId(int $a_child_id): void
    {
        $this->child_id = $a_child_id;
    }

    public function getChildId(): int
    {
        return $this->child_id;
    }

    public function getProperties(): array
    {
        $props = [];

        $maySee = $this->rbacsystem->checkAccess('visible', $this->ref_id);
        $mayRead = $this->rbacsystem->checkAccess('read', $this->ref_id);

        if (!$maySee && !$mayRead) {
            return $props;
        }

        $props = parent::getProperties();

        if (!$mayRead || ilObject::lookupOfflineStatus($this->obj_id)) {
            return $props;
        }

        $this->lng->loadLanguageModule('forum');

        $properties = ilObjForumAccess::getStatisticsByRefId($this->ref_id);
        $num_posts_total = $properties['num_posts'];
        $num_unread_total = $properties['num_unread_posts'];

        $num_drafts_total = 0;
        if (ilForumPostDraft::isSavePostDraftAllowed()) {
            $drafts_statistics = ilForumPostDraft::getDraftsStatisticsByRefId($this->ref_id);
            $num_drafts_total = $drafts_statistics['total'];
        }

        $frm_overview_setting = (int) $this->settings->get('forum_overview');
        $num_new_total = 0;
        if ($frm_overview_setting === ilForumProperties::FORUM_OVERVIEW_WITH_NEW_POSTS) {
            $num_new_total = $properties['num_new_posts'];
        }

        if (!$this->user->isAnonymous()) {
            if ($this->getDetailsLevel() === ilObjectListGUI::DETAILS_ALL) {
                $props[] = [
                    'alert' => false,
                    'property' => $this->lng->txt('forums_articles') . ' (' . $this->lng->txt('unread') . ')',
                    'value' => $num_posts_total . ' (' . $num_unread_total . ')'
                ];
                if ($frm_overview_setting === ilForumProperties::FORUM_OVERVIEW_WITH_NEW_POSTS && $num_new_total > 0) {
                    $props[] = [
                        'alert' => false,
                        'property' => $this->lng->txt('forums_new_articles'),
                        'value' => $num_new_total
                    ];
                }
            }

            if ($num_drafts_total > 0 && ilForumPostDraft::isSavePostDraftAllowed()) {
                $props[] = [
                    'alert' => false,
                    'property' => $this->lng->txt('drafts'),
                    'value' => $num_drafts_total
                ];
            }
        } else {
            $props[] = [
                'alert' => false,
                'property' => $this->lng->txt('forums_articles'),
                'value' => $num_posts_total
            ];
        }

        if (
            $this->getDetailsLevel() === ilObjectListGUI::DETAILS_ALL &&
            ilForumProperties::getInstance($this->obj_id)->isAnonymized()
        ) {
            $props[] = [
                'alert' => false,
                'newline' => false,
                'property' => $this->lng->txt('forums_anonymized'),
                'value' => $this->lng->txt('yes')
            ];
        }

        $last_post = ilObjForumAccess::getLastPostByRefId($this->ref_id);
        if (is_array($last_post) && $last_post['pos_pk'] > 0) {
            $lpCont = "<a class=\"il_ItemProperty\" target=\"" . ilFrameTargetInfo::_getFrame('MainContent') .
                "\" href=\"ilias.php?baseClass=" . ilRepositoryGUI::class . "&amp;cmd=viewThread&amp;cmdClass=" .
                ilObjForumGUI::class . "&amp;target=true&amp;pos_pk=" .
                $last_post['pos_pk'] . "&amp;thr_pk=" . $last_post['pos_thr_fk'] . "&amp;ref_id=" .
                $this->ref_id . "#" . $last_post["pos_pk"] . "\">" .
                ilObjForumAccess::prepareMessageForLists($last_post['pos_message']) . "</a> " .
                strtolower($this->lng->txt('from')) . "&nbsp;";

            $authorinfo = new ilForumAuthorInformation(
                (int) $last_post['pos_author_id'],
                (int) $last_post['pos_display_user_id'],
                (string) $last_post['pos_usr_alias'],
                (string) $last_post['import_name'],
                [
                    'class' => 'il_ItemProperty',
                    'href' => 'ilias.php?baseClass=' . ilRepositoryGUI::class . '&amp;cmd=showUser&amp;' .
                        'cmdClass=' . ilObjForumGUI::class . '&amp;ref_id=' . $this->ref_id . '&amp;' .
                        'user=' . $last_post['pos_display_user_id'] . '&amp;offset=0&amp;backurl=' .
                        urlencode('ilias.php?baseClass=' . ilRepositoryGUI::class . '&amp;ref_id=' . $this->ref_id)
                ]
            );

            $lpCont .= $authorinfo->getLinkedAuthorName();
            $lpCont .= ', ' . ilDatePresentation::formatDate(new ilDateTime($last_post['pos_date'], IL_CAL_DATETIME));

            $props[] = [
                'alert' => false,
                'newline' => true,
                'property' => $this->lng->txt('forums_last_post'),
                'value' => $lpCont
            ];
        }

        return $props;
    }

    public function getCommandFrame(string $cmd): string
    {
        return ilFrameTargetInfo::_getFrame('MainContent');
    }

    public function getCommandLink(string $cmd): string
    {
        switch ($cmd) {
            case 'thread':
                return (
                    'ilias.php?baseClass=' . ilRepositoryGUI::class . '&amp;cmd=viewThread&amp;cmdClass=' .
                    ilObjForumGUI::class . '&amp;ref_id=' . $this->ref_id . '&amp;thr_pk=' . $this->getChildId()
                );

            case 'posting':
                $thread_post = $this->getChildId();
                // TODO PHP 8 This cannot be correct, an id which is used as an array (don't know what is the correct code here) ...
                return (
                    'ilias.php?baseClass=' . ilRepositoryGUI::class . '&amp;cmd=viewThread&amp;cmdClass=' .
                    ilObjForumGUI::class . '&amp;target=1&amp;ref_id=' . $this->ref_id . '&amp;thr_pk=' .
                    $thread_post[0] . '&amp;pos_pk=' . $thread_post[1] . '#' . $thread_post[1]
                );

            default:
                return parent::getCommandLink($cmd);
        }
    }
}
