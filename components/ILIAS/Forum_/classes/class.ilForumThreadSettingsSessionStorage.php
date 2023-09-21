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

class ilForumThreadSettingsSessionStorage
{
    public function __construct(private string $key)
    {
    }

    /**
     * @return array<string, mixed>
     */
    private function getSessionCollection(): array
    {
        $frm_sess = ilSession::get('frm_sess');
        if (!is_array($frm_sess)) {
            $frm_sess = [];
        }

        if (!isset($frm_sess[$this->key]) || !is_array($frm_sess[$this->key])) {
            $frm_sess[$this->key] = [];
        }

        return $frm_sess;
    }

    /**
     * @param mixed|null $default
     * @return mixed
     */
    public function get(int $thread_id, $default = null)
    {
        $frm_sess = $this->getSessionCollection();

        return $frm_sess[$this->key][$thread_id] ?? $default;
    }

    /**
     * @param mixed $value
     */
    public function set(int $thread_id, $value): void
    {
        $frm_sess = $this->getSessionCollection();

        $frm_sess[$this->key][$thread_id] = $value;

        ilSession::set('frm_sess', $frm_sess);
    }
}
