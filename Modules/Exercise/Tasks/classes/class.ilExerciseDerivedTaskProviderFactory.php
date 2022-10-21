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
    public function getProviders(): array
    {
        return [
            new ilExerciseDerivedTaskProvider(
                $this->task_service,
                $this->access,
                $this->lng,
                new ilExerciseDerivedTaskAction(
                    new ilExcMemberRepository(),
                    new ilExcAssMemberStateRepository(),
                    new ilExcTutorRepository(),
                    new \ILIAS\Exercise\Submission\SubmissionDBRepository()
                )
            )
        ];
    }
}
