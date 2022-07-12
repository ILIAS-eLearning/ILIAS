<?php declare(strict_types=1);

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
