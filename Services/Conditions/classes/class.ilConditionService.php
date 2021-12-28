<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Condition service
 * @author @leifos.de
 * @ingroup
 */
class ilConditionService
{
    protected ilConditionObjectAdapterInterface $cond_obj_adapter;

    /**
     * Constructor
     */
    protected function __construct(?ilConditionObjectAdapterInterface $cond_obj_adapter = null)
    {
        if (is_null($cond_obj_adapter)) {
            $this->cond_obj_adapter = new ilConditionObjectAdapter();
        }
    }

    public static function getInstance(ilConditionObjectAdapterInterface $cond_obj_adapter = null) : ilConditionService
    {
        return new self($cond_obj_adapter);
    }

    public function factory() : ilConditionFactory
    {
        return new ilConditionFactory($this->cond_obj_adapter);
    }

    public function util() : ilConditionUtil
    {
        return new ilConditionUtil();
    }
}
