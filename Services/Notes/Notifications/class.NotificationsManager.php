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

namespace ILIAS\Notes;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class NotificationsManager
{
    protected \ilAccessHandler $access;
    protected \ilSetting $settings;
    protected InternalDomainService $domain;
    protected InternalRepoService $repo;
    protected InternalDataService $data;

    public function __construct(
        InternalDataService $data,
        InternalRepoService $repo,
        InternalDomainService $domain
    ) {
        $this->data = $data;
        $this->repo = $repo;
        $this->domain = $domain;
        $this->settings = $domain->settings();
        $this->access = $domain->access();
    }

    /**
     * Sends all comments to a list of accounts
     * configured in the global administration.
     * @todo: get rid of object type specific code
     */
    public function sendNotifications(
        Note $note,
        bool $a_changed = false
    ): void {
        $settings = $this->settings;
        $access = $this->access;

        $obj_title = "";
        $type_lv = "";

        // no notifications for notes
        if ($note->getType() === Note::PRIVATE) {
            return;
        }

        $recipients = $settings->get("comments_noti_recip");
        $recipients = explode(",", $recipients);

        // blog: blog_id, 0, "blog"
        // lm: lm_id, page_id, "pg" (ok)
        // sahs: sahs_id, node_id, node_type
        // info_screen: obj_id, 0, obj_type (ok)
        // portfolio: port_id, page_id, "portfolio_page" (ok)
        // wiki: wiki_id, wiki_page_id, "wpg" (ok)

        $context = $note->getContext();
        $rep_obj_id = $context->getObjId();
        $sub_obj_id = $context->getSubObjId();
        $obj_type = $context->getType();

        // repository objects, no blogs
        $ref_ids = array();
        if (($sub_obj_id === 0 && $obj_type !== "blp") || in_array($obj_type, array("pg", "wpg"), true)) {
            $obj_title = \ilObject::_lookupTitle($rep_obj_id);
            $type_lv = "obj_" . $obj_type;
            $ref_ids = \ilObject::_getAllReferences($rep_obj_id);
        }

        if ($obj_type === "wpg") {
            $type_lv = "obj_wiki";
        }
        if ($obj_type === "pg") {
            $type_lv = "obj_lm";
        }
        if ($obj_type === "blp") {
            $obj_title = \ilObject::_lookupTitle($rep_obj_id);
            $type_lv = "obj_blog";
        }
        if ($obj_type === "pfpg") {
            $obj_title = \ilObject::_lookupTitle($rep_obj_id);
            $type_lv = "portfolio";
        }
        if ($obj_type === "dcl") {
            $obj_title = \ilObject::_lookupTitle($rep_obj_id);
            $type_lv = "obj_dcl";
        }

        foreach ($recipients as $r) {
            $login = trim($r);
            if (($user_id = \ilObjUser::_lookupId($login)) > 0) {
                $link = "";
                foreach ($ref_ids as $ref_id) {
                    if ($access->checkAccessOfUser($user_id, "read", "", $ref_id)) {
                        if ($sub_obj_id === 0 && $obj_type !== "blog") {
                            $link = \ilLink::_getLink($ref_id);
                        } elseif ($obj_type === "wpg") {
                            $title = \ilWikiPage::lookupTitle($sub_obj_id);
                            $link = \ilLink::_getStaticLink(
                                $ref_id,
                                "wiki",
                                true,
                                "_" . \ilWikiUtil::makeUrlTitle($title)
                            );
                        } elseif ($obj_type === "pg") {
                            $link = ILIAS_HTTP_PATH . '/goto.php?client_id=' . CLIENT_ID . "&target=pg_" . $sub_obj_id . "_" . $ref_id;
                        }
                    }
                }
                if ($obj_type === "blp") {
                    // todo
                }
                if ($obj_type === "pfpg") {
                    $link = ILIAS_HTTP_PATH . '/goto.php?client_id=' . CLIENT_ID . "&target=prtf_" . $rep_obj_id;
                }

                // use language of recipient to compose message
                $ulng = \ilLanguageFactory::_getLanguageOfUser($user_id);
                $ulng->loadLanguageModule('note');

                if ($a_changed) {
                    $subject = sprintf($ulng->txt('note_comment_notification_subjectc'), $obj_title . " (" . $ulng->txt($type_lv) . ")");
                } else {
                    $subject = sprintf($ulng->txt('note_comment_notification_subject'), $obj_title . " (" . $ulng->txt($type_lv) . ")");
                }
                $message = sprintf($ulng->txt('note_comment_notification_salutation'), \ilObjUser::_lookupFullname($user_id)) . "\n\n";

                $message .= sprintf($ulng->txt('note_comment_notification_user_has_written'), \ilUserUtil::getNamePresentation($note->getAuthor())) . "\n\n";

                $message .= $note->getText() . "\n\n";

                if ($link !== "") {
                    $message .= $ulng->txt('note_comment_notification_link') . ": " . $link . "\n\n";
                }

                $message .= $ulng->txt('note_comment_notification_reason') . "\n\n";

                $mail_obj = new \ilMail(ANONYMOUS_USER_ID);
                $mail_obj->appendInstallationSignature(true);
                $mail_obj->enqueue(
                    \ilObjUser::_lookupLogin($user_id),
                    "",
                    "",
                    $subject,
                    $message,
                    array()
                );
            }
        }
    }

    /**
     * Notify observers on update/create
     */
    public function notifyObserver(
        array $observer,
        string $action,
        Note $note
    ): void {
        foreach ($observer as $item) {
            $context = $note->getContext();
            $param[] = $context->getObjId();
            $param[] = $context->getSubObjId();
            $param[] = $context->getType();
            $param[] = $action;
            $param[] = $note->getId();
            call_user_func_array($item, $param);
        }
    }
}
