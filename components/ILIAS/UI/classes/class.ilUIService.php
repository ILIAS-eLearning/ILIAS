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
use Psr\Http\Message\ServerRequestInterface;

/**
 * Filter service
 *
 * @author killing@leifos.de
 * @ingroup ServiceUI
 */
class ilUIService
{
    protected ilUIServiceDependencies $_deps;

    public function __construct(ServerRequestInterface $request, UIServices $ui)
    {
        $this->_deps = new ilUIServiceDependencies($ui, new ilUIFilterRequestAdapter($request));
    }

    public function filter(): ilUIFilterService
    {
        return new ilUIFilterService($this, $this->_deps);
    }
}
