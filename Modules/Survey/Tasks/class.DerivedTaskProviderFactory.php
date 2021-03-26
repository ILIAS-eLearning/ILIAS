<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Survey\Tasks;

/**
 * Survey derived task provider factory
 *
 * @author killing@leifos.de
 */
class DerivedTaskProviderFactory implements \ilDerivedTaskProviderFactory
{
    /**
     * @var \ilTaskService
     */
    protected $task_service;

    /**
     * @var \ilAccess
     */
    protected $access;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct(\ilTaskService $task_service, \ilAccess $access = null, \ilLanguage $lng = null)
    {
        global $DIC;

        $this->access = is_null($access)
            ? $DIC->access()
            : $access;

        $this->lng = is_null($lng)
            ? $DIC->language()
            : $lng;

        $this->task_service = $task_service;
    }

    /**
     * @inheritdoc
     */
    public function getProviders() : array
    {
        return [
            new DerivedTaskProvider(
                $this->task_service,
                $this->access,
                $this->lng
            )
        ];
    }
}
