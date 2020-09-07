<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history service
 *
 * @author killing@leifos.de
 * @ingroup ServiceLearningHistory
 */
class ilLearningHistoryService
{
    /**
     * @var ilObjUser
     */
    protected $current_user;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * Constructor
     * @param ilObjUser $user
     * @param ilLanguage $lng
     * @param \ILIAS\DI\UIServices $ui
     * @param ilAccessHandler $access
     */
    public function __construct(ilObjUser $user, ilLanguage $lng, \ILIAS\DI\UIServices $ui, ilAccessHandler $access, ilTree $tree)
    {
        $this->current_user = $user;
        $this->lng = $lng;
        $this->ui = $ui;
        $this->access = $access;
        $this->tree = $tree;
    }

    /**
     * Get tree
     *
     * @return ilTree
     */
    public function repositoryTree()
    {
        return $this->tree;
    }

    /**
     * Get access
     *
     * @return ilAccessHandler
     */
    public function access()
    {
        return $this->access;
    }

    /**
     * Get current user
     *
     * @return ilObjUser
     */
    public function user()
    {
        return $this->current_user;
    }

    /**
     * Get language object
     *
     * @return ilLanguage
     */
    public function language()
    {
        return $this->lng;
    }

    /**
     * Get ui service
     *
     * @return \ILIAS\DI\UIServices
     */
    public function ui()
    {
        return $this->ui;
    }

    /**
     * Factory for learning history entries
     *
     * @return ilLearningHistoryFactory
     */
    public function factory()
    {
        return new ilLearningHistoryFactory($this);
    }

    /**
     * Provider
     *
     * @return ilLearningHistoryProviderFactory
     */
    public function provider()
    {
        return new ilLearningHistoryProviderFactory($this);
    }

    /**
     * Is the service active? The service will be active, if any of its providers are active.
     *
     * @param int $user_id
     * @return bool
     */
    public function isActive(int $user_id = 0)
    {
        global $DIC;

        $setting = $DIC->settings();
        if ($setting->get("enable_learning_history") !== "1") {
            return false;
        }

        if ($user_id = 0) {
            $user_id = $this->user()->getId();
        }
        foreach ($this->provider()->getAllProviders(true, $user_id) as $p) {
            return true;
        }
        return false;
    }
}
