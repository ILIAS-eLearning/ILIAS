<?php declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilMMItemStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemStorage extends CachedActiveRecord
{

    /**
     * @param isItem $item
     *
     * @return ilMMItemStorage
     */
    public static function register(isItem $item) : ilMMItemStorage
    {
        if ($item instanceof Lost) {
            return new self();
        }

        $mm_item = ilMMItemStorage::find($item->getProviderIdentification()->serialize());
        if ($mm_item === null) {
            $mm_item = new ilMMItemStorage();
            $mm_item->setPosition($item->getPosition());
            $mm_item->setIdentification($item->getProviderIdentification()->serialize());
            $mm_item->setActive(true);
            if ($item instanceof isChild) {
                $mm_item->setParentIdentification($item->getParent()->serialize());
            }
            $mm_item->create();
        }

        return $mm_item;
    }




    public function create() : void
    {
        if (self::find($this->getIdentification())) {
            $this->update();
        } else {
            parent::create();
        }
    }


    /**
     * @inheritDoc
     */
    public function getCache() : ilGlobalCache
    {
        return ilGlobalCache::getInstance(ilGlobalCache::COMP_GLOBAL_SCREEN);
    }


    /**
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     64
     */
    protected ?string $identification;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected bool $active = true;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     */
    protected int $position = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected ?string $parent_identification = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected ?string $icon_id = '';
    /**
     * @var string
     */
    protected string $connector_container_name = "il_mm_items";


    /**
     * @return string
     */
    public function getIdentification() : string
    {
        return $this->identification;
    }


    /**
     * @param string $identification
     */
    public function setIdentification(string $identification)
    {
        $this->identification = $identification;
    }


    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->active;
    }


    /**
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }


    /**
     * @return int
     */
    public function getPosition() : int
    {
        return $this->position;
    }


    /**
     * @param int $position
     */
    public function setPosition(int $position)
    {
        $this->position = $position;
    }


    /**
     * @return string
     */
    public function getParentIdentification() : string
    {
        return $this->parent_identification;
    }


    /**
     * @param string $parent_identification
     */
    public function setParentIdentification(string $parent_identification)
    {
        $this->parent_identification = $parent_identification;
    }


    /**
     * @return string
     */
    public function getIconId() : ?string
    {
        return $this->icon_id === '' ? null : $this->icon_id;
    }


    /**
     * @param string $icon_id
     *
     * @return ilMMItemStorage
     */
    public function setIconId(string $icon_id) : ilMMItemStorage
    {
        $this->icon_id = $icon_id;

        return $this;
    }
}
