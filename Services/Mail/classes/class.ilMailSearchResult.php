<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailSearchResult
{
    /** @var array[] */
    protected array $result = [];

    public function __construct()
    {
    }

    public function addItem(int $id, array $fields) : void
    {
        $this->result[$id] = $fields;
    }

    /**
     * @return int[]
     */
    public function getIds() : array
    {
        return array_keys($this->result);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getFields(int $id) : array
    {
        if (!isset($this->result[$id])) {
            throw new OutOfBoundsException('mail_missing_result_fields');
        }
        
        return $this->result[$id];
    }
}
