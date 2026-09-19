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

declare(strict_types=1);

namespace ILIAS\Repository\Administration;

use ILIAS\Repository\InternalGUIService;
use ILIAS\Repository\InternalDomainService;
use ILIAS\Repository\Administration\Table\NewItemGroupTableBuilder;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIService
{
    protected InternalGUIService $gui_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->gui_service = $gui_service;
        $this->domain_service = $domain_service;
    }

    public function request(): AdministrationGUIRequest
    {
        return new AdministrationGUIRequest(
            $this->gui_service->http(),
            $this->domain_service->refinery()
        );
    }

    public function newItemGroupTableBuilder(
        bool $has_write_permission,
        object $parent_gui,
        string $parent_cmd
    ): NewItemGroupTableBuilder {
        return new NewItemGroupTableBuilder(
            $this->domain_service,
            $this->gui_service,
            $has_write_permission,
            $parent_gui,
            $parent_cmd
        );
    }
}
