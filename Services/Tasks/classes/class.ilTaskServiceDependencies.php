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

use ILIAS\DI\UIServices;

/**
 * Task service dependencies
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaskServiceDependencies
{
    protected ilLanguage $lng;

    protected UIServices $ui;

    protected ilObjUser $user;

    protected ilAccessHandler $access;

    protected ilDerivedTaskProviderMasterFactory $derived_task_provider_master_factory;

    /**
     * Constructor
     */
    public function __construct(
        ilObjUser $user,
        ilLanguage $lng,
        UIServices $ui,
        ilAccessHandler $access,
        ilDerivedTaskProviderMasterFactory $derived_task_provider_master_factory
    ) {
        $this->lng = $lng;
        $this->ui = $ui;
        $this->user = $user;
        $this->access = $access;
        $this->derived_task_provider_master_factory = $derived_task_provider_master_factory;
    }

    /**
     * Get derived task provider master factory
     */
    public function getDerivedTaskProviderMasterFactory() : ilDerivedTaskProviderMasterFactory
    {
        return $this->derived_task_provider_master_factory;
    }

    /**
     * Get language object
     */
    public function language() : ilLanguage
    {
        return $this->lng;
    }

    /**
     * Get current user
     */
    public function user() : ilObjUser
    {
        return $this->user;
    }

    /**
     * Get ui service
     */
    public function ui() : UIServices
    {
        return $this->ui;
    }

    /**
     * Get access
     */
    protected function getAccess() : ilAccessHandler
    {
        return $this->access;
    }
}
