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
 * Class ilBlogDerivedTaskProviderFactory
 * @author Thomas Famula <famula@leifos.de>
 */
class ilBlogDerivedTaskProviderFactory implements ilDerivedTaskProviderFactory
{
    protected ilTaskService $taskService;
    protected \ilAccess $accessHandler;
    protected \ilLanguage $lng;

    public function __construct(
        ilTaskService $taskService,
        \ilAccess $accessHandler = null,
        \ilLanguage $lng = null
    ) {
        global $DIC;

        $this->taskService = $taskService;

        $this->accessHandler = is_null($accessHandler)
            ? $DIC->access()
            : $accessHandler;

        $this->lng = is_null($lng)
            ? $DIC->language()
            : $lng;
    }

    public function getProviders(): array
    {
        return [
            new ilBlogDraftsDerivedTaskProvider(
                $this->taskService,
                $this->accessHandler,
                $this->lng
            )
        ];
    }
}
