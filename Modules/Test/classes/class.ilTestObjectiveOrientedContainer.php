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
    public function getObjId(): ?int
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
    public function getRefId(): ?int
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
    public function isObjectiveOrientedPresentationRequired(): bool
    {
        return (bool) $this->getObjId();
    }
}
