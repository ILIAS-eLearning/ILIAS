<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAutoCompleteRecipientProvider
 */
abstract class ilMailAutoCompleteRecipientProvider implements Iterator
{
    protected ilDBInterface $db;
    protected ?ilDBStatement $res = null;
    /** @var string[] */
    protected array $data = [];
    protected $quoted_term = '';
    protected string $term = '';
    protected int $user_id = 0;

    public function __construct(string $quoted_term, string $term)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->quoted_term = $quoted_term;
        $this->term = $term;
        $this->user_id = $DIC->user()->getId();
    }

    public function valid() : bool
    {
        $this->data = $this->db->fetchAssoc($this->res);

        return is_array($this->data) && !empty($this->data);
    }

    public function next() : void
    {
    }

    public function __destruct()
    {
        if ($this->res) {
            $this->db->free($this->res);
            $this->res = null;
        }
    }
}
