<?php declare(strict_types=1);
/* Copyright (c) 2021 Extended GPL, see docs/LICENSE */

require_once(__DIR__ . '/../../../../libs/composer/vendor/autoload.php');
require_once(__DIR__ . '/../../Base.php');

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Test items contributions
 */
class ItemContributionTest extends ILIAS_UI_TestBase
{
    public function getFactory() : C\Item\Factory
    {
        return new I\Component\Item\Factory;
    }

    public function getUserMock() : ilObjUser
    {
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $user->method('getPublicName')->willReturn('Test User (testy)');
        return $user;
    }

    public function getDateTimeMock() : ilDateTime
    {
        $user = $this->getMockBuilder(ilDateTime::class)->disableOriginalConstructor()->getMock();
        $user->method('get')->with(IL_CAL_UNIX)->willReturn(0);
        $user->method('get')->with($this->any())->willReturn('1.2.3456');
        return $user;
    }

    public function test_implements_factory_interface() : void
    {
        $f = $this->getFactory();

        $contribution = $f->contribution('contribution');

        $this->assertInstanceOf('ILIAS\\UI\\Component\\Item\\Contribution', $contribution);
    }

    public function test_get_description() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution('contribution');
        $this->assertEquals('contribution', $c->getDescription());
    }

    public function test_get_user() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution(
            'contribution',
            $this->getUserMock()
        );
        $this->assertInstanceOf(ilObjUser::class, $c->getUser());
    }

    public function test_get_datetime() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution(
            'contribution',
            null,
            $this->getDateTimeMock()
        );
        $this->assertInstanceOf(ilDateTime::class, $c->getDateTime());
    }

    public function test_get_close() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution('contribution')->withClose(
            (new I\Component\Button\Factory())->close()
        );
        $this->assertInstanceOf(I\Component\Button\Close::class, $c->getClose());
    }

    public function test_get_lead_icon() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution('contribution')->withLeadIcon(
            (new I\Component\Symbol\Icon\Factory())->standard('name', 'label')
        );
        $this->assertInstanceOf(I\Component\Symbol\Icon\Icon::class, $c->getLeadIcon());
    }

    public function test_get_identifier() : void
    {
        $f = $this->getFactory();
        $c = $f->contribution('contribution')->withIdentifier('testid');
        $this->assertEquals('testid', $c->getIdentifier());
    }
}
