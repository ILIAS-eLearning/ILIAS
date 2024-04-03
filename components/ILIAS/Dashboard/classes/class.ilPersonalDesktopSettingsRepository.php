<?php

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

declare(strict_types=1);

use ILIAS\Administration\Setting;

class ilPersonalDesktopSettingsRepository
{
    protected Setting $settings;

    public function __construct(Setting $settings)
    {
        $this->settings = $settings;
    }


    public function ifNotesEnabled(): bool
    {
        return !$this->settings->get('disable_notes');
    }

    public function enableNotes(bool $active = true): void
    {
        $this->settings->set('disable_notes', $active ? '0' : '1');
    }

    public function ifCommentsEnabled(): bool
    {
        return !$this->settings->get('disable_comments');
    }

    public function enableComments(bool $active = true): void
    {
        $this->settings->set('disable_comments', $active ? '0' : '1');
    }

    public function ifAuthorsCanDelete(): bool
    {
        return (bool) $this->settings->get('comments_del_user', '0');
    }

    public function enableAuthorsCanDelete(bool $active = true): void
    {
        $this->settings->set('comments_del_user', $active ? '1' : '0');
    }

    public function ifTutorsCanDelete(): bool
    {
        return (bool) $this->settings->get('comments_del_tutor', '1');
    }

    public function enableTutorsCanDelete(bool $active = true): void
    {
        $this->settings->set('comments_del_tutor', $active ? '1' : '0');
    }

    public function getCommentsNotificationRecipients(): string
    {
        return (string) $this->settings->get('comments_noti_recip');
    }

    public function updateCommentsNotificationRecipients(string $recipients): void
    {
        $this->settings->set('comments_noti_recip', $recipients);
    }

    public function ifLearningHistoryEnabled(): bool
    {
        return (bool) $this->settings->get('enable_learning_history');
    }

    public function enableLearningHistory(bool $active = true): void
    {
        $this->settings->set('enable_learning_history', $active ? '1' : '0');
    }

    public function ifChatViewerEnabled(): bool
    {
        return (bool) $this->settings->get('block_activated_chatviewer');
    }

    public function enableChatViewer(bool $active = true): void
    {
        $this->settings->set('block_activated_chatviewer', $active ? '1' : '0');
    }

    public function getSystemMessagePresentation(): int
    {
        return (int) $this->settings->get('pd_sys_msg_mode');
    }

    public function updateSystemMessagePresentation(int $mode): void
    {
        $this->settings->set('pd_sys_msg_mode', (string) $mode);
    }

    public function ifForumDrafts(): bool
    {
        return (bool) $this->settings->get('block_activated_pdfrmpostdraft', '0');
    }

    public function enableForumDrafts(bool $active = true): void
    {
        $this->settings->set('block_activated_pdfrmpostdraft', $active ? '1' : '0');
    }
}
