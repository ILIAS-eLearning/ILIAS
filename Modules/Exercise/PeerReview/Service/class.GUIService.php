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

namespace ILIAS\Exercise\PeerReview;

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;

class GUIService
{
    protected InternalDomainService $domain_service;
    protected InternalGUIService $gui_service;


    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->domain_service = $domain_service;
        $this->gui_service = $gui_service;
    }


    public function getPeerReviewGUI(\ilExAssignment $exc, ?\ilExSubmission $submission = null): \ilExPeerReviewGUI
    {
        return new \ilExPeerReviewGUI(
            $exc,
            $submission
        );
    }
}
