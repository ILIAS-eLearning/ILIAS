<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;

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
            if ($item instanceof \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild) {
                $mm_item->setParentIdentification($item->getParent()->serialize());
            }
            $mm_item->create();
        }

        return $mm_item;
    }


    public function create()
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
     * @var string
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     64
     */
    protected $identification;
    /**
     * @var bool
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $active = true;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     */
    protected $position = 0;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $parent_identification = '';
    /**
     * @var string
     */
    protected $connector_container_name = "il_mm_items";


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
}
