<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesMail
 */
class ilMailSearchResult
{
    protected array $result = [];

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param array $item
     */
    public function addItem(int $id, array $fields) : void
    {
        $this->result[$id] = $fields;
    }

    /**
     * @return array
     */
    public function getIds() : array
    {
        return array_keys($this->result);
    }

    /**
     * @param int $id
     * @return array
     * @throws OutOfBoundsException
     */
    public function getFields(int $id) : array
    {
        if (!isset($this->result[$id])) {
            throw new OutOfBoundsException('mail_missing_result_fields');
        }
        
        return $this->result[$id];
    }
}
