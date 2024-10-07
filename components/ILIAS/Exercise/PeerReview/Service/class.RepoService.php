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

use ILIAS\Exercise\IRSS\IRSSWrapper;
use ILIAS\Exercise\InternalDataService;
use ILIAS\Exercise\PeerReview\Criteria\CriteriaFileRepository;

class RepoService
{
    public function __construct(
        protected IRSSWrapper $irss_wrapper,
        protected InternalDataService $data,
        protected \ilDBInterface $db
    ) {
    }

    public function criteriaFile(): CriteriaFileRepository
    {
        return new CriteriaFileRepository(
            $this->irss_wrapper,
            $this->data,
            $this->db
        );
    }
}
