<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Personal desktop settings repo
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPersonalDesktopSettingsRepository
{
    protected \ILIAS\Administration\Setting $settings;

    public function __construct(\ILIAS\Administration\Setting $settings)
    {
        $this->settings = $settings;
    }


    // Notes enabled?
    public function ifNotesEnabled(): bool
    {
        return !$this->settings->get("disable_notes");
    }

    public function enableNotes(bool $active = true): void
    {
        $this->settings->set("disable_notes", (int) !$active);
    }

    // Comments enabled?
    public function ifCommentsEnabled(): bool
    {
        return !$this->settings->get("disable_comments");
    }

    public function enableComments(bool $active = true): void
    {
        $this->settings->set("disable_comments", (int) !$active);
    }

    // Can authors delete their comments
    public function ifAuthorsCanDelete(): bool
    {
        return (bool) $this->settings->get("comments_del_user", '0');
    }

    public function enableAuthorsCanDelete(bool $active = true): void
    {
        $this->settings->set("comments_del_user", (int) $active);
    }

    // Can tutors delete comments of others
    public function ifTutorsCanDelete(): bool
    {
        return (bool) $this->settings->get("comments_del_tutor", '1');
    }

    public function enableTutorsCanDelete(bool $active = true): void
    {
        $this->settings->set("comments_del_tutor", (int) $active);
    }

    // Get recipients of comments notification
    public function getCommentsNotificationRecipients(): string
    {
        return (string) $this->settings->get("comments_noti_recip");
    }

    // Update recipients of comments notification
    public function updateCommentsNotificationRecipients(string $recipients): void
    {
        $this->settings->set("comments_noti_recip", $recipients);
    }

    // learning history enabled?
    public function ifLearningHistoryEnabled(): bool
    {
        return (bool) $this->settings->get("enable_learning_history");
    }

    public function enableLearningHistory(bool $active = true): void
    {
        $this->settings->set("enable_learning_history", (int) $active);
    }

    // chat viewer enabled?
    public function ifChatViewerEnabled(): bool
    {
        return (bool) $this->settings->get("block_activated_chatviewer");
    }

    public function enableChatViewer(bool $active = true): void
    {
        $this->settings->set("block_activated_chatviewer", (int) $active);
    }

    public function getSystemMessagePresentation(): int
    {
        return (int) $this->settings->get("pd_sys_msg_mode");
    }

    public function updateSystemMessagePresentation(int $mode): void
    {
        $this->settings->set("pd_sys_msg_mode", $mode);
    }

    // forum draft block enabled?
    public function ifForumDrafts(): bool
    {
        return (bool) $this->settings->get('block_activated_pdfrmpostdraft', '0');
    }

    public function enableForumDrafts(bool $active = true): void
    {
        $this->settings->set("block_activated_pdfrmpostdraft", (int) $active);
    }
}
