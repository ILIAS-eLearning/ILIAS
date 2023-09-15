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

namespace ILIAS\Blog\Exercise;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIService
{
    protected InternalGUIService $gui;
    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDataService $data_service,
        InternalDomainService $domain_service,
        InternalGUIService $gui
    ) {
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->gui = $gui;
    }

    public function ilBlogExerciseGUI(int $a_node_id): \ilBlogExerciseGUI
    {
        return new \ilBlogExerciseGUI(
            $a_node_id,
            $this->domain_service->exercise($a_node_id),
            $this->domain_service->lng(),
            $this->domain_service->user(),
            $this->gui
        );
    }
}
