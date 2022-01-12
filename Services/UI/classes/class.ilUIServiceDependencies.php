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
 ********************************************************************
 */

use ILIAS\DI\UIServices;

/**
 * UI service dependencies
 *
 * @author killing@leifos.de
 * @ingroup ServiceUI
 */
class ilUIServiceDependencies
{
    protected ilUIFilterRequestAdapter $request_adapter;
    protected ilUIFilterServiceSessionGateway $session;
    protected UIServices $ui;

    public function __construct(
        UIServices $ui,
        ilUIFilterRequestAdapter $request,
        ?ilUIFilterServiceSessionGateway $session = null
    ) {
        $this->ui = $ui;
        $this->request_adapter = $request;
        $this->session = $session ?? new ilUIFilterServiceSessionGateway();
    }

    public function ui() : UIServices
    {
        return $this->ui;
    }

    public function getRequest() : ilUIFilterRequestAdapter
    {
        return $this->request_adapter;
    }

    public function getSession() : ilUIFilterServiceSessionGateway
    {
        return $this->session;
    }
}
