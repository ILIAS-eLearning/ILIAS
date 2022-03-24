<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Exercise derived task provider factory
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseDerivedTaskProviderFactory implements ilDerivedTaskProviderFactory
{
    protected ilTaskService $task_service;
    protected ilAccess $access;
    protected ilLanguage $lng;

    public function __construct(
        ilTaskService $task_service,
        ilAccess $access = null,
        ilLanguage $lng = null
    ) {
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
     * @return \ilExerciseDerivedTaskProvider[]
     */
    public function getProviders() : array
    {
        return [
            new ilExerciseDerivedTaskProvider(
                $this->task_service,
                $this->access,
                $this->lng,
                new ilExerciseDerivedTaskAction(
                    new ilExcMemberRepository(),
                    new ilExcAssMemberStateRepository(),
                    new ilExcTutorRepository()
                )
            )
        ];
    }
}
