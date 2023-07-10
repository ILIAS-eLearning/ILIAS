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
 * Learning history service
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningHistoryService
{
    protected ilObjUser $current_user;
    protected ilLanguage $lng;
    protected \ILIAS\DI\UIServices $ui;
    protected ilAccessHandler $access;
    protected ilTree $tree;

    public function __construct(
        ilObjUser $user,
        ilLanguage $lng,
        \ILIAS\DI\UIServices $ui,
        ilAccessHandler $access,
        ilTree $tree
    ) {
        $this->current_user = $user;
        $this->lng = $lng;
        $this->ui = $ui;
        $this->access = $access;
        $this->tree = $tree;
    }

    public function request(): \ILIAS\LearningHistory\StandardGUIRequest
    {
        global $DIC;

        return new \ILIAS\LearningHistory\StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    public function repositoryTree(): ilTree
    {
        return $this->tree;
    }

    public function access(): ilAccessHandler
    {
        return $this->access;
    }

    public function user(): ilObjUser
    {
        return $this->current_user;
    }

    public function language(): ilLanguage
    {
        return $this->lng;
    }

    public function ui(): \ILIAS\DI\UIServices
    {
        return $this->ui;
    }

    /**
     * Factory for learning history entries
     */
    public function factory(): ilLearningHistoryFactory
    {
        return new ilLearningHistoryFactory($this);
    }

    public function provider(): ilLearningHistoryProviderFactory
    {
        return new ilLearningHistoryProviderFactory($this);
    }

    /**
     * Is the service active? The service will be active, if any of its providers are active.
     */
    public function isActive(int $user_id = 0): bool
    {
        global $DIC;

        $setting = $DIC->settings();
        if ($setting->get("enable_learning_history") !== "1") {
            return false;
        }

        if ($user_id === 0) {
            $user_id = $this->user()->getId();
        }

        return count($this->provider()->getAllProviders(true, $user_id)) > 0;
    }
}
