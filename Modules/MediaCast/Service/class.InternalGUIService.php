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

namespace ILIAS\MediaCast;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;
use ILIAS\MediaCast\Comments;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalGUIService
{
    use GlobalDICGUIServices;

    protected \ILIAS\Notes\GUIService $notes_gui;
    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        Container $DIC,
        InternalDataService $data_service,
        InternalDomainService $domain_service
    ) {
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->initGUIServices($DIC);
        $this->notes_gui = $DIC->notes()->gui();
    }

    /*public function administration() : Administration\GUIService
    {
        return new Administration\GUIService(
            $this->domain_service,
            $this
        );
    }*/

    public function standardRequest(): StandardGUIRequest
    {
        return new StandardGUIRequest(
            $this->http(),
            $this->domain_service->refinery()
        );
    }

    public function getObjMediaCastGUI(): \ilObjMediaCastGUI
    {
        return new \ilObjMediaCastGUI(
            "",
            $this->standardRequest()->getRefId(),
            true,
            false
        );
    }

    public function getMediaCastManageTableGUI(\ilObjMediaCastGUI $gui, string $table_cmd): \ilMediaCastManageTableGUI
    {
        return new \ilMediaCastManageTableGUI($gui, $table_cmd);
    }

    public function comments(): Comments\GUIService
    {
        return new Comments\GUIService(
            $this->domain_service,
            $this,
            $this->notes_gui
        );
    }
}
