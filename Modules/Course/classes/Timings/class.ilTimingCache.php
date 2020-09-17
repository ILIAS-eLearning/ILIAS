<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* class ilTimingCache
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/
class ilTimingCache
{
    /**
     * @var null | ilTimingCache
     */
    private static $instances = array();

    /**
     * @var int
     */
    private $ref_id = 0;

    /**
     * @var int
     */
    private $obj_id = 0;

    /**
     * @var bool
     */
    private $timings_active = false;

    /**
     * @var array
     */
    private $timings = array();

    /**
     * @var array
     */
    private $timings_user = array();

    /**
     * @var array
     */
    private $collection_items = array();

    /**
     * @var array
     */
    private $completed_users = array();

    /**
     * ilTimingCache constructor.
     */
    public function __construct($ref_id)
    {
        $this->ref_id = $ref_id;
        $this->obj_id = ilObject::_lookupObjId($this->ref_id);
        $this->readObjectInformation();
    }

    /**
     * @param $ref_id
     * @return ilTimingCache
     */
    public static function getInstanceByRefId($ref_id)
    {
        if (!isset(self::$instances[$ref_id])) {
            self::$instances[$ref_id] = new self($ref_id);
        }
        return self::$instances[$ref_id];
    }

    /**
     * @param int $usr_id
     * @return bool
     */
    public function isWarningRequired($usr_id)
    {
        if (in_array($usr_id, $this->completed_users)) {
            return false;
        }
        foreach ($this->collection_items as $item) {
            $item_instance = self::getInstanceByRefId($item);
            if ($item_instance->isWarningRequired($usr_id)) {
                return true;
            }
        }
        if (!$this->timings_active) {
            return false;
        }

        // check constraints
        if ($this->timings['changeable'] && isset($this->timings_user[$usr_id]['end'])) {
            $end = $this->timings_user[$usr_id]['end'];
        } else {
            $end = $this->timings['suggestion_end'];
        }
        return $end < time();
    }

    /**
     * Read timing information for object
     */
    protected function readObjectInformation()
    {
        $this->timings = ilObjectActivation::getItem($this->ref_id);
        $this->timings_active = false;
        if ($this->timings['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) {
            $this->timings_active = true;
            $this->timings_user = ilTimingPlaned::_getPlanedTimingsByItem($this->ref_id);
        }

        $olp = ilObjectLP::getInstance($this->obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection instanceof ilLPCollectionOfRepositoryObjects) {
            $this->collection_items = $collection->getItems();
        }
        $this->completed_users = ilLPStatus::_getCompleted($this->obj_id);
    }


    /**
     * @deprecated 7
     * @param $a_ref_id
     * @return mixed
     */
    public static function &_getTimings($a_ref_id)
    {
        static $cache = array();

        if (isset($cache[$a_ref_id])) {
            return $cache[$a_ref_id];
        }
        $cache[$a_ref_id]['item'] = ilObjectActivation::getItem($a_ref_id);
        $cache[$a_ref_id]['user'] = ilTimingPlaned::_getPlanedTimingsByItem($a_ref_id);

        return $cache[$a_ref_id];
    }
        
}
