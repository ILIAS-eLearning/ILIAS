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

namespace ILIAS\Forum\Posting\Test;

use ILIAS\Forum\Posting\BindingDecision;
use ILIAS\Forum\Posting\BindingDenial;
use LogicException;
use PHPUnit\Framework\TestCase;

class BindingDecisionTest extends TestCase
{
    public function testGrantedDecisionRefusesToNameADenialReason(): void
    {
        $this->expectException(LogicException::class);

        BindingDecision::granted()->denialReason();
    }

    public function testDeniedDecisionKeepsItsReason(): void
    {
        $decision = BindingDecision::denied(BindingDenial::FOREIGN_FORUM);

        $this->assertFalse($decision->isGranted());
        $this->assertSame(BindingDenial::FOREIGN_FORUM, $decision->denialReason());
    }
}
