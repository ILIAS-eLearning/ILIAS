<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Cache/classes/class.ilCache.php");

/**
 * Caches (check) access information on list items.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesObject
 */
class ilListItemAccessCache extends ilCache
{
    /**
     * @var ilSetting
     */
    protected $settings;

    public static $disabled = false;
    
    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct()
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $ilSetting = $DIC->settings();
        parent::__construct("ServicesObject", "CheckAccess", false);
        //		$this->setExpiresAfter($ilSetting->get("rep_cache") * 60);
        $this->setExpiresAfter(0);
        //		if ((int) $ilSetting->get("rep_cache") == 0)
        if (true) {
            self::$disabled = true;
        }
    }
    
    /**
     * Check if cache is disabled
     * @return
     */
    public function isDisabled()
    {
        return self::$disabled or parent::isDisabled();
    }
    
    
    /**
     * Read an entry
     */
    public function readEntry($a_id)
    {
        if (!$this->isDisabled()) {
            return parent::readEntry($a_id);
        }
        return false;
    }
    
    
    /**
     * Id is user_id:ref_id, we store ref_if additionally
     */
    public function storeEntry(
        $a_id,
        $a_value,
        $a_ref_id = 0,
        $a_int_key2 = null,
        $a_text_key1 = null,
        $a_text_key2 = null
    ) {
        if (!$this->isDisabled()) {
            parent::storeEntry($a_id, $a_value, $a_ref_id);
        }
    }

    /**
     * This one can be called, e.g.
     */
    public function deleteByRefId($a_ref_id)
    {
        parent::deleteByAdditionalKeys($a_ref_id);
    }
}
