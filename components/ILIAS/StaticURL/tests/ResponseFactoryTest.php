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

namespace ILIAS\StaticURL\Tests;

use ILIAS\DI\Container;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\CannotReach;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Response\MaybeCanHandlerAfterLogin;

require_once "Base.php";

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ResponseFactoryTest extends Base
{
    private function buildContext(bool $logged_in): Context
    {
        $user = $this->createMock(\ilObjUser::class);
        $user->method('isAnonymous')->willReturn(!$logged_in);
        $user->method('getId')->willReturn($logged_in ? 42 : 0);

        $container = $this->createMock(Container::class);
        $container->method('user')->willReturn($user);

        return new Context($container);
    }

    public function testCannotReturnsCannotHandle(): void
    {
        $response = (new Factory($this->buildContext(true)))->cannot();

        $this->assertFalse($response->targetCanBeReached());
        $this->assertNull($response->getURIPath());
    }

    public function testCannotReachReturnsCannotReach(): void
    {
        $response = (new Factory($this->buildContext(true)))->cannotReach();

        $this->assertTrue($response->targetCanBeReached());
        $this->assertNull($response->getURIPath());
    }

    public function testLoginFirstReturnsCannotReachForLoggedInUser(): void
    {
        $response = (new Factory($this->buildContext(true)))->loginFirst();

        $this->assertInstanceOf(CannotReach::class, $response);
    }

    public function testLoginFirstReturnsMaybeCanHandlerAfterLoginForAnonymous(): void
    {
        $response = (new Factory($this->buildContext(false)))->loginFirst();

        $this->assertInstanceOf(MaybeCanHandlerAfterLogin::class, $response);
    }

    public function testCanReturnsCanHandleWithURIPathWithoutShift(): void
    {
        $response = (new Factory($this->buildContext(true)))->can('/ilias.php?ref_id=5');

        $this->assertSame('/ilias.php?ref_id=5', $response->getURIPath());
        $this->assertSame(0, $response->shift());
        $this->assertTrue($response->targetCanBeReached());
    }

    public function testCanReturnsCanHandleWithURIPathWithShift(): void
    {
        $response = (new Factory($this->buildContext(true)))->can('/ilias.php?ref_id=5', true);

        $this->assertSame(1, $response->shift());
    }
}
