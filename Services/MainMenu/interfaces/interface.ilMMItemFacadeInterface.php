<?php

/**
 * Interface ilMMItemFacadeInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilMMItemFacadeInterface
{

    //
    // Access to related objects
    //
    public function itemStorage() : ilMMItemStorage;
    
    public function getRawItem() : \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
    
    public function getFilteredItem() : \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
    
    public function identification() : \ILIAS\GlobalScreen\Identification\IdentificationInterface;


    //
    // Presentation Methods
    //

    /**
     * @return string
     */
    public function getTypeForPresentation() : string;

    /**
     * @return string
     */
    public function getProviderNameForPresentation() : string;

    /**
     * @return string
     */
    public function getStatus() : string;


    //
    // Getters
    //
    /**
     * @return bool
     */
    public function isAvailable() : bool;

    /**
     * @return bool
     */
    public function isActivated() : bool;

    /**
     * @return bool
     */
    public function isEditable() : bool;

    /**
     * @return bool
     */
    public function isDeletable() : bool;

    /**
     * @return bool
     */
    public function isAlwaysAvailable() : bool;

    /**
     * @return string
     */
    public function getDefaultTitle() : string;

    /**
     * @return string
     */
    public function getId() : string;

    /**
     * @return int
     */
    public function getAmountOfChildren() : int;

    /**
     * @return bool
     */
    public function hasStorage() : bool;

    /**
     * @return bool
     */
    public function supportsRoleBasedVisibility() : bool;

    /**
     * @return bool
     */
    public function hasRoleBasedVisibility() : bool;

    /**
     * @return array
     */
    public function getGlobalRoleIDs() : array;

    /**
     * @param array $global_role_ids
     */
    public function setGlobalRoleIDs(array $global_role_ids) : void;

    /**
     * @param bool $role_based_visibility
     */
    public function setRoleBasedVisibility(bool $role_based_visibility) : void;

    /**
     * @return bool
     */
    public function isEmpty() : bool;

    /**
     * @return bool
     */
    public function isCustom() : bool;

    /**
     * @return bool
     */
    public function supportsCustomIcon() : bool;

    /**
     * @return bool
     */
    public function isCustomType() : bool;

    /**
     * @return string
     */
    public function getParentIdentificationString() : string;

    /**
     * @return string FQ Classname
     */
    public function getType() : string;

    /**
     * @return bool
     */
    public function isTopItem() : bool;

    /**
     * @return bool
     */
    public function isChild() : bool;

    /**
     * @return bool
     */
    public function isInLostItem() : bool;

    public function getIconID() : ?string;


    //
    // Setters
    //
    /**
     * @param string $action
     */
    public function setAction(string $action);

    /**
     * @param bool $status
     */
    public function setActiveStatus(bool $status);

    /**
     * @param string $default_title
     */
    public function setDefaultTitle(string $default_title);

    /**
     * @param string $icon_id
     * @return mixed
     */
    public function setIconID(string $icon_id);

    /**
     * @param int $position
     */
    public function setPosition(int $position);

    /**
     * @param string $parent
     */
    public function setParent(string $parent);

    /**
     * @param string $type
     */
    public function setType(string $type);

    /**
     * @param bool $top_item ;
     */
    public function setIsTopItm(bool $top_item);

    /**
     * @return bool
     */
    public function isInterchangeable() : bool;

    //
    // CRUD
    //
    /**
     * @return void
     */
    public function update();

    /**
     * @return void
     */
    public function create();

    /**
     * @return void
     */
    public function delete();
}
