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
use ILIAS\Exercise\PeerReview\Criteria\CriteriaCatalogueTableBuilder;
use ILIAS\Exercise\PeerReview\Criteria\CriteriaTableBuilder;

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

    public function criteriaCatalogueTableBuilder(
        int $exc_id,
        object $parent_gui,
        string $parent_cmd
    ): CriteriaCatalogueTableBuilder {
        return new CriteriaCatalogueTableBuilder(
            $this->domain_service,
            $this->gui_service,
            $exc_id,
            $parent_gui,
            $parent_cmd
        );
    }

    public function criteriaTableBuilder(
        int $cat_id,
        object $parent_gui,
        string $parent_cmd
    ): CriteriaTableBuilder {
        return new CriteriaTableBuilder(
            $this->domain_service,
            $this->gui_service,
            $cat_id,
            $parent_gui,
            $parent_cmd
        );
    }
}
