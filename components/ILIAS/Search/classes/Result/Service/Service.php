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

namespace ILIAS\Search\Result\Service;

use ILIAS\DI\Container;
use ILIAS\Search\Result\Filter\Filter as ResultFilter;
use ILIAS\Search\Result\Filter\FilterImpl as ResultFilterImpl;
use ILIAS\Search\Result\Filter\Criterion\Criterion as ResultFiltercriterion;
use ILIAS\Search\Result\Filter\Criterion\UserIsPublic;

class Service
{
    protected ResultFilter $result_filter;
    protected UserIsPublic $user_is_public_criterion;

    public function __construct(
        protected Container $dic
    ) {
    }

    public function resultFilter(): ResultFilter
    {
        return $this->result_filter ??= new ResultFilterImpl();
    }

    public function userIsPublicCriterion(): ResultFiltercriterion
    {
        return new UserIsPublic(
            $this->dic['user']->getSettings(),
            $this->dic['user']->getProfile()
        );
    }
}
