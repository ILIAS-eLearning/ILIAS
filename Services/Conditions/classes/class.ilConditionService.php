<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Condition service
 *
 * @author @leifos.de
 * @ingroup
 */
class ilConditionService
{
    /**
     * @var ilConditionObjectAdapterInterface
     */
    protected $cond_obj_adapter;

    /**
     * Constructor
     */
    protected function __construct(ilConditionObjectAdapterInterface $cond_obj_adapter = null)
    {
        if (is_null($cond_obj_adapter)) {
            $this->cond_obj_adapter = new ilConditionObjectAdapter();
        }
    }

    /**
     * Get instance
     *
     * @return ilConditionService
     */
    public static function getInstance(ilConditionObjectAdapterInterface $cond_obj_adapter = null)
    {
        return new self($cond_obj_adapter);
    }

    /**
     * factory
     *
     * @return ilConditionFactory
     */
    public function factory()
    {
        return new ilConditionFactory($this->cond_obj_adapter);
    }

    /**
     * utilities
     *
     * @return ilConditionUtil
     */
    public function util()
    {
        return new ilConditionUtil();
    }
}
