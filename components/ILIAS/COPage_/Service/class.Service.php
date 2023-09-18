<?php

declare(strict_types=1);

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

namespace ILIAS\COPage;

use ILIAS\DI\Container;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class Service
{
    protected InternalService $internal;
    protected Container $DIC;

    public function __construct(Container $DIC)
    {
        $this->DIC = $DIC;
        $this->internal = new InternalService($this->DIC);
    }

    /**
     * Internal service, do not use in other components
     */
    public function internal(): InternalService
    {
        return $this->internal;
    }
}
