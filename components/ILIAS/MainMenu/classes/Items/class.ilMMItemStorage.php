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

declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;

/**
 * Class ilMMItemStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemStorage extends CachedActiveRecord
{
    public static function register(isItem $item): ilMMItemStorage
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




    #[\Override]
    public function create(): void
    {
        parent::create();
        if (self::find($this->getIdentification()) !== null) {
            $this->update();
        } else {
        }
    }


    /**
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     64
     */
    protected ?string $identification = null;
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
    protected string $connector_container_name = "il_mm_items";


    public function getIdentification(): string
    {
        return $this->identification;
    }


    public function setIdentification(string $identification): void
    {
        $this->identification = $identification;
    }


    public function isActive(): bool
    {
        return $this->active;
    }


    public function setActive(bool $active): void
    {
        $this->active = $active;
    }


    public function getPosition(): int
    {
        return $this->position;
    }


    public function setPosition(int $position): void
    {
        $this->position = $position;
    }


    public function getParentIdentification(): string
    {
        return $this->parent_identification;
    }


    public function setParentIdentification(string $parent_identification): void
    {
        $this->parent_identification = $parent_identification;
    }


    /**
     * @return string
     */
    public function getIconId(): ?string
    {
        return $this->icon_id === '' ? null : $this->icon_id;
    }


    public function setIconId(string $icon_id): ilMMItemStorage
    {
        $this->icon_id = $icon_id;

        return $this;
    }
}
