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

namespace ILIAS\Awareness\User;

/**
 * Represents a set of collected users
 * @author Alexander Killing <killing@leifos.de>
 */
class Collection implements \Countable
{
    /** @var int[] */
    protected array $users = array();

    /**
     * @param int $a_id user id
     */
    public function addUser(int $a_id): void
    {
        $this->users[$a_id] = $a_id;
    }

    /**
     * @param int $a_id user id
     */
    public function removeUser(int $a_id): void
    {
        if (isset($this->users[$a_id])) {
            unset($this->users[$a_id]);
        }
    }

    /**
     * @return int[] array of user ids
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    public function count(): int
    {
        return count($this->users);
    }
}
