<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilForumThreadSettinsSessionStorage
{
    private string $key;

    public function __construct(string $session_key)
    {
        $this->key = $session_key;
    }

    /**
     * @param int $thread_id
     * @param mixed|null $default
     * @return mixed
     */
    public function get(int $thread_id, $default = null)
    {
        $frm_sess = (array) ilSession::get('frm_sess');

        return $frm_sess[$this->key][$thread_id] ?? $default;
    }

    /**
     * @param int $thread_id
     * @param mixed $value
     */
    public function set(int $thread_id, $value) : void
    {
        $frm_sess = (array) ilSession::get('frm_sess');

        $frm_sess[$this->key] = $value;

        ilSession::set('frm_sess', $frm_sess);
    }
}
