<?php declare(strict_types = 1);

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

namespace ILIAS\Notes;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class AccessManager
{
    protected \ilAccessHandler $access;
    protected \ilSetting $settings;
    protected int $user_id;
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
        $this->user_id = $domain->user()->getId();
        $this->settings = $domain->settings();
        $this->access = $domain->access();
    }

    public function canEdit(
        Note $note,
        int $user_id = 0
    ) : bool {
        if ($user_id === 0) {
            $user_id = $this->user_id;
        }
        return $user_id !== ANONYMOUS_USER_ID && $note->getAuthor() === $user_id;
    }

    public function canDelete(
        Note $note,
        int $user_id = 0,
        $public_deletion_enabled = false
    ) : bool {
        if ($user_id === 0) {
            $user_id = $this->user_id;
        }
        $settings = $this->settings;
        $access = $this->access;
        $user_can_delete_their_comments = (bool) $settings->get("comments_del_user", '0');
        $tutor_can_delete_comments = (bool) $settings->get("comments_del_tutor", '1');

        if ($user_id === ANONYMOUS_USER_ID) {
            return false;
        }

        $is_author = ($note->getAuthor() === $user_id);

        if ($is_author && $note->getType() === Note::PRIVATE) {
            return true;
        }

        if ($public_deletion_enabled && $note->getType() === Note::PUBLIC) {
            return true;
        }

        if ($is_author && $user_can_delete_their_comments && $note->getType() === Note::PUBLIC) {
            return true;
        }

        // this logic has been set from pdnotes
        if ($tutor_can_delete_comments) {
            foreach (\ilObject::_getAllReferences($note->getContext()->getObjId()) as $ref_id) {
                if ($access->checkAccess("write", "", $ref_id)) {
                    return true;
                }
            }
        }
        return false;
    }
}
