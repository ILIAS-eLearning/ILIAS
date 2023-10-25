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

namespace ILIAS\Taxonomy;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;

class InternalGUIService
{
    use GlobalDICGUIServices;

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
    }

    public function settings(): \ILIAS\Taxonomy\Settings\GUIService
    {
        return new \ILIAS\Taxonomy\Settings\GUIService(
            $this->domain_service,
            $this
        );
    }

    public function getObjTaxonomyGUI(int $rep_obj_id): \ilObjTaxonomyGUI
    {
        $tax_gui = new \ilObjTaxonomyGUI();
        $tax_gui->setAssignedObject($rep_obj_id);
        return $tax_gui;
    }


}
