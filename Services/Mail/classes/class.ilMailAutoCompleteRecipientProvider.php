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
 * Class ilMailAutoCompleteRecipientProvider
 */
abstract class ilMailAutoCompleteRecipientProvider implements Iterator
{
    protected ilDBInterface $db;
    protected ?ilDBStatement $res = null;
    /** @var null|array{login?: string, firstname?: string, lastname?: string} */
    protected ?array $data = null;
    protected int $user_id = 0;

    public function __construct(protected string $quoted_term, protected string $term)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user_id = $DIC->user()->getId();
    }

    public function valid(): bool
    {
        $this->data = $this->db->fetchAssoc($this->res);

        return is_array($this->data) && !empty($this->data);
    }

    public function next(): void
    {
    }

    public function __destruct()
    {
        if ($this->res !== null) {
            $this->db->free($this->res);
            $this->res = null;
        }
    }
}
