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

/**
 * Class ilMMCustomItemStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMCustomItemStorage extends CachedActiveRecord
{

    /**
     * @var string
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected $identifier = '';
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     128
     */
    protected $type = '';
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     4000
     */
    protected $action = "";
    /**
     * @var bool
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $role_based_visibility = false;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     4000
     */
    protected $global_role_ids = "";
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     4000
     */
    protected $default_title = "";
    /**
     * @var bool
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $top_item = false;
    /**
     * @var string
     */
    protected $connector_container_name = "il_mm_custom_items";


    /**
     * @return string
     */
    public function getIdentifier() : string
    {
        return $this->identifier;
    }


    /**
     * @param string $identifier
     *
     * @return ilMMCustomItemStorage
     */
    public function setIdentifier(string $identifier) : ilMMCustomItemStorage
    {
        $this->identifier = $identifier;

        return $this;
    }


    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }


    /**
     * @param string $type
     *
     * @return ilMMCustomItemStorage
     */
    public function setType(string $type) : ilMMCustomItemStorage
    {
        $this->type = $type;

        return $this;
    }


    /**
     * @return bool
     */
    public function isTopItem() : bool
    {
        return $this->top_item;
    }


    /**
     * @param bool $top_item
     *
     * @return ilMMCustomItemStorage
     */
    public function setTopItem(bool $top_item) : ilMMCustomItemStorage
    {
        $this->top_item = $top_item;

        return $this;
    }


    /**
     * @return string
     */
    public function getAction() : string
    {
        return $this->action;
    }


    /**
     * @param string $action
     *
     * @return ilMMCustomItemStorage
     */
    public function setAction(string $action) : ilMMCustomItemStorage
    {
        $this->action = $action;

        return $this;
    }


    /**
     * @return bool
     */
    public function hasRoleBasedVisibility() : bool
    {
        if ($this->role_based_visibility !== null) {
            return $this->role_based_visibility;
        } else {
            return false;
        }
    }


    /**
     * @param bool $role_based_visibility
     *
     * @return ilMMCustomItemStorage
     */
    public function setRoleBasedVisibility(bool $role_based_visibility) : ilMMCustomItemStorage
    {
        $this->role_based_visibility = $role_based_visibility;

        return $this;
    }


    /**
     * @return array
     */
    public function getGlobalRoleIDs() : array
    {
        return array_map("intval", explode(",", $this->global_role_ids));
    }


    /**
     * @param array $global_role_ids
     *
     * @return ilMMCustomItemStorage
     */
    public function setGlobalRoleIDs(array $global_role_ids) : ilMMCustomItemStorage
    {
        $this->global_role_ids = implode(",", $global_role_ids);

        return $this;
    }


    /**
     * @return string
     */
    public function getDefaultTitle() : string
    {
        return $this->default_title;
    }


    /**
     * @param string $default_title
     *
     * @return ilMMCustomItemStorage
     */
    public function setDefaultTitle(string $default_title) : ilMMCustomItemStorage
    {
        $this->default_title = $default_title;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getCache() : ilGlobalCache
    {
        return ilGlobalCache::getInstance(ilGlobalCache::COMP_GLOBAL_SCREEN);
    }
}
