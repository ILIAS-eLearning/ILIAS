<?php

/**
 * Class ilMMTypeActionStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTypeActionStorage extends CachedActiveRecord
{

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
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     4000
     */
    protected $action = '';
    /**
     * @var bool
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $external = false;
    /**
     * @var string
     */
    protected $connector_container_name = "il_mm_actions";


    /**
     * @return string
     */
    public function getIdentification() : string
    {
        return $this->identification;
    }


    /**
     * @param string $identification
     *
     * @return ilMMTypeActionStorage
     */
    public function setIdentification(string $identification) : ilMMTypeActionStorage
    {
        $this->identification = $identification;

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
     * @return ilMMTypeActionStorage
     */
    public function setAction(string $action) : ilMMTypeActionStorage
    {
        $this->action = $action;

        return $this;
    }


    /**
     * @return bool
     */
    public function isExternal() : bool
    {
        return $this->external;
    }


    /**
     * @param bool $external
     *
     * @return ilMMTypeActionStorage
     */
    public function setExternal(bool $external) : ilMMTypeActionStorage
    {
        $this->external = $external;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getCache() : ilGlobalCache
    {
        return ilGlobalCache::getInstance(ilGlobalCache::COMP_GLOBAL_SCREEN);
    }


    /**
     * @return ilMMTypeActionStorage
     */
    public static function find($primary_key, array $add_constructor_args = array())
    {
        $parent = parent::find($primary_key, $add_constructor_args);
        if ($parent === null) {
            $parent = new self();
            $parent->setIdentification($primary_key);
            $parent->create();
        }

        return $parent;
    }
}
