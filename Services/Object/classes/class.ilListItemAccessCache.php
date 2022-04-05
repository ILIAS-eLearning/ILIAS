<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Caches (check) access information on list items.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilListItemAccessCache extends ilCache
{
    public static bool $disabled = false;

    protected ilSetting $settings;

    public function __construct()
    {
        global $DIC;

        $this->settings = $DIC->settings();
        parent::__construct("ServicesObject", "CheckAccess");
        $this->setExpiresAfter(0);
        self::$disabled = true;
    }
    
    /**
     * Check if cache is disabled
     */
    public function isDisabled() : bool
    {
        return self::$disabled or parent::isDisabled();
    }
    
    /**
     * Read an entry
     */
    protected function readEntry(string $id) : bool
    {
        if (!$this->isDisabled()) {
            return parent::readEntry($id);
        }
        return false;
    }
    
    
    /**
     * Id is user_id:ref_id, we store ref_if additionally
     */
    public function storeEntry(
        string $id,
        string $value,
        ?int $int_key1 = null,
        ?int $int_key2 = null,
        ?string $text_key1 = null,
        ?string $text_key2 = null
    ) : void {
        if (!$this->isDisabled()) {
            parent::storeEntry($id, $value, $int_key1);
        }
    }

    /**
     * This one can be called, e.g.
     */
    public function deleteByRefId(int $ref_id) : void
    {
        parent::deleteByAdditionalKeys($ref_id);
    }
}
