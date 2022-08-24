<?php

declare(strict_types=1);

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

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Interface ilMMItemFacadeInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilMMItemFacadeInterface
{
    //
    // Access to related objects
    //
    public function itemStorage(): ilMMItemStorage;

    public function getRawItem(): isItem;

    public function getFilteredItem(): isItem;

    public function identification(): IdentificationInterface;


    //
    // Presentation Methods
    //

    /**
     * @return string
     */
    public function getTypeForPresentation(): string;

    /**
     * @return string
     */
    public function getProviderNameForPresentation(): string;

    /**
     * @return string
     */
    public function getStatus(): string;


    //
    // Getters
    //
    /**
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * @return bool
     */
    public function isActivated(): bool;

    /**
     * @return bool
     */
    public function isEditable(): bool;

    /**
     * @return bool
     */
    public function isDeletable(): bool;

    /**
     * @return bool
     */
    public function isAlwaysAvailable(): bool;

    /**
     * @return string
     */
    public function getDefaultTitle(): string;

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return int
     */
    public function getAmountOfChildren(): int;

    /**
     * @return bool
     */
    public function hasStorage(): bool;

    /**
     * @return bool
     */
    public function supportsRoleBasedVisibility(): bool;

    /**
     * @return bool
     */
    public function hasRoleBasedVisibility(): bool;

    /**
     * @return array
     */
    public function getGlobalRoleIDs(): array;

    /**
     * @param array $global_role_ids
     */
    public function setGlobalRoleIDs(array $global_role_ids): void;

    /**
     * @param bool $role_based_visibility
     */
    public function setRoleBasedVisibility(bool $role_based_visibility): void;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return bool
     */
    public function isCustom(): bool;

    /**
     * @return bool
     */
    public function supportsCustomIcon(): bool;

    /**
     * @return bool
     */
    public function isCustomType(): bool;

    /**
     * @return string
     */
    public function getParentIdentificationString(): string;

    /**
     * @return string FQ Classname
     */
    public function getType(): string;

    /**
     * @return bool
     */
    public function isTopItem(): bool;

    /**
     * @return bool
     */
    public function isChild(): bool;

    /**
     * @return bool
     */
    public function isInLostItem(): bool;

    public function getIconID(): ?string;


    //
    // Setters
    //
    /**
     * @param string $action
     */
    public function setAction(string $action): void;

    /**
     * @param bool $status
     */
    public function setActiveStatus(bool $status): void;

    /**
     * @param string $default_title
     */
    public function setDefaultTitle(string $default_title): void;

    /**
     * @param string $icon_id
     * @return void
     */
    public function setIconID(string $icon_id): void;

    /**
     * @param int $position
     */
    public function setPosition(int $position): void;

    /**
     * @param string $parent
     */
    public function setParent(string $parent): void;

    /**
     * @param string $type
     */
    public function setType(string $type): void;

    /**
     * @param bool $top_item ;
     */
    public function setIsTopItm(bool $top_item): void;

    /**
     * @return bool
     */
    public function isInterchangeable(): bool;

    //
    // CRUD
    //
    /**
     * @return void
     */
    public function update(): void;

    /**
     * @return void
     */
    public function create(): void;

    /**
     * @return void
     */
    public function delete(): void;
}
