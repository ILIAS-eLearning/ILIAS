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

namespace ILIAS\Wiki\Notification;

use ILIAS\Wiki\InternalGUIService;
use ILIAS\Wiki\InternalDomainService;

/**
 * This class is only in GUI layer, since it
 * needs to get the abstracts for the page GUI
 */
class NotificationGUI
{
    protected \ilLogger $log;
    protected InternalGUIService $gui;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->gui = $gui_service;
        $this->domain = $domain_service;
        $this->log = $domain_service->log();
    }

    public function send(
        string $a_action,
        int $a_type,
        int $a_wiki_ref_id,
        int $a_page_id,
        ?string $a_comment = null,
        string $lang = "-"
    ): void {
        global $DIC;

        if ($a_wiki_ref_id === 0) {
            return;
        }

        if ($lang === "") {
            $lang = "-";
        }

        $log = $this->log;
        $log->debug("start... vvvvvvvvvvvvvvvvvvvvvvvvvvv");

        $ilUser = $this->domain->user();
        $ilAccess = $this->domain->access();

        $wiki = $this->domain->wiki()->object($a_wiki_ref_id);
        $wiki_id = $wiki->getId();
        $log->debug("page id: " . $a_page_id);
        $log->debug("lang: " . $lang);
        $pgui = $this->gui->page()->getWikiPageGUI(
            $a_wiki_ref_id,
            $a_page_id,
            0,
            $lang
        );
        $page = $pgui->getPageObject();

        // #11138
        $ignore_threshold = ($a_action === "comment");

        // 1st update will be converted to new - see below
        if ($a_action === "new") {
            return;
        }

        $log->debug("-- get notifications");
        if ($a_type === \ilNotification::TYPE_WIKI_PAGE) {
            $users = \ilNotification::getNotificationsForObject($a_type, $a_page_id, null, $ignore_threshold);
            $wiki_users = \ilNotification::getNotificationsForObject(\ilNotification::TYPE_WIKI, $wiki_id, $a_page_id, $ignore_threshold);
            $users = array_merge($users, $wiki_users);
            if (!count($users)) {
                $log->debug("no notifications... ^^^^^^^^^^^^^^^^^^");
                return;
            }
            \ilNotification::updateNotificationTime(\ilNotification::TYPE_WIKI_PAGE, $a_page_id, $users);
        } else {
            $users = \ilNotification::getNotificationsForObject(\ilNotification::TYPE_WIKI, $wiki_id, $a_page_id, $ignore_threshold);
            if (!count($users)) {
                $log->debug("no notifications... ^^^^^^^^^^^^^^^^^^");
                return;
            }
        }

        \ilNotification::updateNotificationTime(\ilNotification::TYPE_WIKI, $wiki_id, $users, $a_page_id);

        // #15192 - should always be present
        if ($a_page_id) {
            // #18804 - see ilWikiPageGUI::preview()
            $link = \ilLink::_getLink(null, "wiki", [], "wpage_" . $a_page_id . "_" . $a_wiki_ref_id);
        } else {
            $link = \ilLink::_getLink($a_wiki_ref_id);
        }

        $log->debug("-- prepare content");
        $pgui->setRawPageContent(true);
        $pgui->setAbstractOnly(true);
        $pgui->setFileDownloadLink(".");
        $pgui->setFullscreenLink(".");
        $pgui->setSourcecodeDownloadScript(".");
        $snippet = $pgui->showPage();
        $snippet = \ilPageObject::truncateHTML($snippet, 500, "...");

        // making things more readable
        $snippet = str_replace(['<br/>', '<br />', '</p>', '</div>'], "\n", $snippet);

        $snippet = trim(strip_tags($snippet));

        // "fake" new (to enable snippet - if any)
        $hist = $page->getHistoryEntries();
        $current_version = array_shift($hist);
        $current_version = $current_version["nr"] ?? 0;
        if (!$current_version && $a_action !== "comment") {
            $a_type = \ilNotification::TYPE_WIKI;
            $a_action = "new";
        }

        $log->debug("-- sending mails");
        $mails = [];
        foreach (array_unique($users) as $idx => $user_id) {
            if ($user_id !== $ilUser->getId() &&
                $ilAccess->checkAccessOfUser($user_id, 'read', '', $a_wiki_ref_id)) {
                // use language of recipient to compose message
                $ulng = \ilLanguageFactory::_getLanguageOfUser($user_id);
                $ulng->loadLanguageModule('wiki');

                if ($a_action === "comment") {
                    $subject = sprintf($ulng->txt('wiki_notification_comment_subject'), $wiki->getTitle(), $page->getTitle());
                    $message = sprintf($ulng->txt('wiki_change_notification_salutation'), \ilObjUser::_lookupFullname($user_id)) . "\n\n";

                    $message .= $ulng->txt('wiki_notification_' . $a_action) . ":\n\n";
                    $message .= $ulng->txt('wiki') . ": " . $wiki->getTitle() . "\n";
                    $message .= $ulng->txt('page') . ": " . $page->getTitle() . "\n";
                    $message .= $ulng->txt('wiki_commented_by') . ": " . \ilUserUtil::getNamePresentation($ilUser->getId()) . "\n";

                    // include comment/note text
                    if ($a_comment) {
                        $message .= "\n" . $ulng->txt('comment') . ":\n\"" . trim($a_comment) . "\"\n";
                    }

                    $message .= "\n" . $ulng->txt('wiki_change_notification_page_link') . ": " . $link;
                } else {
                    $subject = sprintf($ulng->txt('wiki_change_notification_subject'), $wiki->getTitle(), $page->getTitle());
                    $message = sprintf($ulng->txt('wiki_change_notification_salutation'), \ilObjUser::_lookupFullname($user_id)) . "\n\n";

                    if ($a_type == \ilNotification::TYPE_WIKI_PAGE) {
                        // update/delete
                        $message .= $ulng->txt('wiki_change_notification_page_body_' . $a_action) . ":\n\n";
                        $message .= $ulng->txt('wiki') . ": " . $wiki->getTitle() . "\n";
                        $message .= $ulng->txt('page') . ": " . $page->getTitle() . "\n";
                        $message .= $ulng->txt('wiki_changed_by') . ": " . \ilUserUtil::getNamePresentation($ilUser->getId()) . "\n";

                        if ($snippet) {
                            $message .= "\n" . $ulng->txt('content') . "\n" .
                                "----------------------------------------\n" .
                                $snippet . "\n" .
                                "----------------------------------------\n";
                        }

                        // include comment/note text
                        if ($a_comment) {
                            $message .= "\n" . $ulng->txt('comment') . ":\n\"" . trim($a_comment) . "\"\n";
                        }

                        $message .= "\n" . $ulng->txt('wiki_change_notification_page_link') . ": " . $link;
                    } else {
                        // new
                        $message .= $ulng->txt('wiki_change_notification_body_' . $a_action) . ":\n\n";
                        $message .= $ulng->txt('wiki') . ": " . $wiki->getTitle() . "\n";
                        $message .= $ulng->txt('page') . ": " . $page->getTitle() . "\n";
                        $message .= $ulng->txt('wiki_changed_by') . ": " . \ilUserUtil::getNamePresentation($ilUser->getId()) . "\n\n";

                        if ($snippet) {
                            $message .= $ulng->txt('content') . "\n" .
                                "----------------------------------------\n" .
                                $snippet . "\n" .
                                "----------------------------------------\n\n";
                        }

                        $message .= $ulng->txt('wiki_change_notification_link') . ": " . $link;
                    }
                }

                $mail_obj = new \ilMail(ANONYMOUS_USER_ID);
                $mail_obj->appendInstallationSignature(true);
                $log->debug("before enqueue ($user_id)");
                /*
                $mail_obj->enqueue(
                    ilObjUser::_lookupLogin($user_id),
                    "",
                    "",
                    $subject,
                    $message,
                    array()
                );*/
                $message .= \ilMail::_getInstallationSignature();
                $mails[] = new \ilMailValueObject(
                    '',
                    \ilObjUser::_lookupLogin($user_id),
                    '',
                    '',
                    $subject,
                    $message,
                    [],
                    false,
                    false
                );
                $log->debug("after enqueue");
            } else {
                unset($users[$idx]);
            }
        }
        if (count($mails) > 0) {
            $processor = new \ilMassMailTaskProcessor();
            $processor->run(
                $mails,
                ANONYMOUS_USER_ID,
                "",
                []
            );
        }
        $log->debug("end... ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^");
    }

}
