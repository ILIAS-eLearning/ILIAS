<?php declare(strict_types=1);

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

use ILIAS\DI\Container;
use ilAwarenessUserProviderContactRequests as Contacts;
use PHPUnit\Framework\MockObject\MockObject;

class ilAwarenessUserProviderContactRequestsTest extends ilBuddySystemBaseTest
{
    /**
     * @var ilObjUser|MockObject
     */
    private $user;

    /**
     * @var ilLanguage|MockObject
     */
    private $language;

    public function testConstruct() : void
    {
        $this->assertInstanceOf(Contacts::class, $this->create());
    }

    public function testGetProviderId() : void
    {
        $this->assertSame('contact_approved', $this->create()->getProviderId());
    }

    public function testGetTitle() : void
    {
        $this->expectTranslation('getTitle', 'contact_awrn_req_contacts');
    }

    public function testGetInfo() : void
    {
        $this->expectTranslation('getInfo', 'contact_awrn_req_contacts_info');
    }

    public function testGetInitialUserSet() : void
    {
        $instance = $this->create();

        $this->user->expects(self::once())->method('isAnonymous')->willReturn(true);

        $this->assertEquals([], $instance->getInitialUserSet());
    }

    public function testIsHighlighted() : void
    {
        $this->assertTrue($this->create()->isHighlighted());
    }

    private function expectTranslation(string $method, string $languageKey) : void
    {
        $expected = 'translated: ' . $languageKey;

        $instance = $this->create();

        $this->language->expects(self::once())->method('loadLanguageModule')->with('contact');
        $this->language->expects(self::once())->method('txt')->with($languageKey)->willReturn($expected);

        $this->assertSame($expected, $instance->$method());
    }

    private function create() : Contacts
    {
        $this->user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $this->language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();

        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('user')->willReturn($this->user);
        $container->expects(self::once())->method('language')->willReturn($this->language);

        return new Contacts($container);
    }
}
