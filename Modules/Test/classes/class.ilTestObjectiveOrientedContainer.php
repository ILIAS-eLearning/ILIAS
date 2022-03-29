<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestObjectiveOrientedContainer
{
    /**
     * @var integer
     */
    private $objId;
    
    /**
     * @var integer
     */
    private $refId;

    public function __construct()
    {
        $this->objId = null;
        $this->refId = null;
    }
    
    /**
     * @return int
     */
    public function getObjId() : ?int
    {
        return $this->objId;
    }

    /**
     * @param int $objId
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;
    }

    /**
     * @return int
     */
    public function getRefId() : ?int
    {
        return $this->refId;
    }

    /**
     * @param int $refId
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;
    }

    /**
     * @return bool
     */
    public function isObjectiveOrientedPresentationRequired() : bool
    {
        return (bool) $this->getObjId();
    }
}
