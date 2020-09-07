<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesMail
 */
class ilMailSearchResult
{
    /**
     * @var array
     */
    protected $result = array();

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param array $item
     */
    public function addItem($id, array $fields)
    {
        $this->result[$id] = $fields;
    }

    /**
     * @return array
     */
    public function getIds()
    {
        return array_keys($this->result);
    }

    /**
     * @param integer $id
     * @return array
     * @throws OutOfBoundsException
     */
    public function getFields($id)
    {
        if (!isset($this->result[$id])) {
            throw new OutOfBoundsException('mail_missing_result_fields');
        }
        
        return $this->result[$id];
    }
}
