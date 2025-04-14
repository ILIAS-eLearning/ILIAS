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

use ILIAS\DI\Container;
use ilAwarenessUserProviderApprovedContacts as ApprovedContacts;
use PHPUnit\Framework\Attributes\Depends;

class ilAwarenessUserProviderApprovedContactsTestCase extends ilBuddySystemBaseTestCase
{
    public function testConstruct(): ApprovedContacts
    {
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects($this->once())->method('user')->willReturn($user);
        $container->expects($this->once())->method('language')->willReturn($language);

        $instance = new ApprovedContacts($container);

        $this->assertInstanceOf(ApprovedContacts::class, $instance);

        return $instance;
    }

    #[Depends('testConstruct')]
    public function testGetProviderId(ApprovedContacts $instance): void
    {
        $this->assertSame('contact_requests', $instance->getProviderId());
    }

    public function testGetTitle(): void
    {
        $this->expectTranslation('getTitle', 'contact_awrn_ap_contacts');
    }

    public function testGetInfo(): void
    {
        $this->expectTranslation('getInfo', 'contact_awrn_ap_contacts_info');
    }

    public function testGetInitialUserSet(): void
    {
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $user->expects($this->once())->method('isAnonymous')->willReturn(true);
        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects($this->once())->method('user')->willReturn($user);
        $container->expects($this->once())->method('language')->willReturn($language);

        $instance = new ApprovedContacts($container);

        $this->assertEquals([], $instance->getInitialUserSet());
    }

    #[Depends('testConstruct')]
    public function testIsHighlighted(ApprovedContacts $instance): void
    {
        $this->assertFalse($instance->isHighlighted());
    }

    private function expectTranslation(string $method, string $languageKey): void
    {
        $expected = 'translated: ' . $languageKey;
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $language->expects($this->once())->method('loadLanguageModule')->with('contact');
        $language->expects($this->once())->method('txt')->with($languageKey)->willReturn($expected);

        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects($this->once())->method('user')->willReturn($user);
        $container->expects($this->once())->method('language')->willReturn($language);

        $instance = new ApprovedContacts($container);
        $this->assertSame($expected, $instance->$method());
    }
}
