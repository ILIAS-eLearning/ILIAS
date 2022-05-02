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
    }

    public function canEdit(?Note $note, int $user_id = 0) : bool
    {
        if (is_null($note)) {
            return false;
        }
        if ($user_id === 0) {
            $user_id = $this->user_id;
        }
        return $user_id !== ANONYMOUS_USER_ID && $note->getAuthor() === $user_id->getId();
    }
}
