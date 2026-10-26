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

namespace ILIAS\FileUpload\Activities;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class AccessCheckTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAUserOfTheSystemIsKnown(): void
    {
        define('ANONYMOUS_USER_ID', 13);

        $this->assertTrue(AccessCheck::isKnownUser(6));
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testNeitherNobodyNorTheAnonymousUserIsKnown(): void
    {
        define('ANONYMOUS_USER_ID', 13);

        $this->assertFalse(AccessCheck::isKnownUser(0));
        $this->assertFalse(AccessCheck::isKnownUser(-1));
        $this->assertFalse(AccessCheck::isKnownUser(13));
    }

    /**
     * Without the id of the anonymous user it cannot be told apart - so
     * nobody is known rather than everybody.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testWithoutTheAnonymousUserNobodyIsKnown(): void
    {
        $this->assertFalse(defined('ANONYMOUS_USER_ID'));
        $this->assertFalse(AccessCheck::isKnownUser(6));
        $this->assertFalse(AccessCheck::isKnownUser(13));
    }
}
