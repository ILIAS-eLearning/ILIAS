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
 * Class ilMailAutoCompleteSentMailsRecipientsProvider
 */
class ilMailAutoCompleteSentMailsRecipientsProvider extends ilMailAutoCompleteRecipientProvider
{
    /** @var string[] */
    protected array $users_stack = [];

    /**
     * @return array{login?: string, firstname?: string, lastname?: string}
     */
    public function current(): array
    {
        if (is_array($this->data)) {
            return [
                'login' => $this->data['login'],
                'firstname' => '',
                'lastname' => '',
            ];
        }

        if ($this->users_stack !== []) {
            return [
                'login' => array_shift($this->users_stack),
                'firstname' => '',
                'lastname' => '',
            ];
        }

        return [
            'login' => '',
            'firstname' => '',
            'lastname' => '',
        ];
    }

    public function key(): string
    {
        if (is_array($this->data) && !empty($this->data)) {
            return $this->data['login'];
        }

        if ($this->users_stack !== []) {
            return $this->users_stack[0];
        }

        return '';
    }

    public function valid(): bool
    {
        $this->data = $this->db->fetchAssoc($this->res);
        if (
            is_array($this->data) &&
            (
                strpos($this->data['login'], ',') ||
                strpos($this->data['login'], ';')
            )
        ) {
            $parts = array_filter(array_map(
                'trim',
                preg_split("/[ ]*[;,][ ]*/", trim($this->data['login']))
            ));

            foreach ($parts as $part) {
                if (ilStr::strPos(ilStr::strToLower($part), ilStr::strToLower($this->term)) !== false) {
                    $this->users_stack[] = $part;
                }
            }

            if ($this->users_stack) {
                $this->data = [];
            }
        }

        return is_array($this->data) || $this->users_stack !== [];
    }

    public function rewind(): void
    {
        if ($this->res !== null) {
            $this->db->free($this->res);
            $this->res = null;
        }

        $query = "
			SELECT DISTINCT
				mail.rcp_to login
			FROM mail
			WHERE " . $this->db->like('mail.rcp_to', 'text', $this->quoted_term) . "
			AND sender_id = " . $this->db->quote($this->user_id, 'integer') . "
			AND mail.sender_id = mail.user_id";

        $this->res = $this->db->query($query);
    }
}
