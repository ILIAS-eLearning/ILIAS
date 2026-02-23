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
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslatableItem;
use ILIAS\UI\Component\Legacy\Content;

/**
 * Interface ilMMItemFacadeInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilMMItemFacadeInterface extends TranslatableItem
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
    public function getTypeForPresentation(): string;

    public function getProviderNameForPresentation(): string;

    public function getStatus(): ?Content;


    //
    // Getters
    //
    public function isAvailable(): bool;

    public function isActivated(): bool;
    public function canBeDeactivated(): bool;

    public function isEditable(): bool;

    public function isDeletable(): bool;

    public function isAlwaysAvailable(): bool;

    public function getDefaultTitle(): string;

    public function getId(): string;

    public function getAmountOfChildren(): int;

    public function hasStorage(): bool;

    public function supportsRoleBasedVisibility(): bool;

    public function hasRoleBasedVisibility(): bool;

    public function getGlobalRoleIDs(): array;

    public function setGlobalRoleIDs(array $global_role_ids): void;

    public function setRoleBasedVisibility(bool $role_based_visibility): void;

    public function isEmpty(): bool;

    public function isCustom(): bool;

    public function supportsCustomIcon(): bool;

    public function isCustomType(): bool;

    public function getParentIdentificationString(): string;

    /**
     * @return string FQ Classname
     */
    public function getType(): string;

    public function isTopItem(): bool;

    public function canHaveChildren(): bool;

    public function isChild(): bool;

    public function isInLostItem(): bool;

    public function getIconID(): ?string;


    //
    // Setters
    //
    public function setAction(string $action): void;

    public function setActiveStatus(bool $status): void;

    public function setDefaultTitle(string $default_title): void;

    public function setIconID(string $icon_id): void;

    public function setPosition(int $position): void;

    public function setParent(string $parent): void;

    public function setType(string $type): void;

    /**
     * @param bool $top_item ;
     */
    public function setIsTopItm(bool $top_item): void;

    public function isInterchangeable(): bool;

    //
    // CRUD
    //
    public function update(): void;

    public function create(): void;

    public function delete(): void;
}
