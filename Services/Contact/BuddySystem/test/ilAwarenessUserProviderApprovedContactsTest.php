<?php declare(strict_types=1);

use ILIAS\DI\Container;
use ilAwarenessUserProviderApprovedContacts as ApprovedContacts;

class ilAwarenessUserProviderApprovedContactsTest extends ilBuddySystemBaseTest
{
    public function testConstruct() : ApprovedContacts
    {
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('user')->willReturn($user);
        $container->expects(self::once())->method('language')->willReturn($language);

        $instance = new ApprovedContacts($container);

        $this->assertInstanceOf(ApprovedContacts::class, $instance);

        return $instance;
    }

    /**
     * @depends testConstruct
     */
    public function testGetProviderId(ApprovedContacts $instance) : void
    {
        $this->assertEquals('contact_requests', $instance->getProviderId());
    }

    public function testGetTitle() : void
    {
        $this->expectTranslation('getTitle', 'contact_awrn_ap_contacts');
    }

    public function testGetInfo() : void
    {
        $this->expectTranslation('getInfo', 'contact_awrn_ap_contacts_info');
    }

    public function testGetInitialUserSet() : void
    {
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $user->expects(self::once())->method('isAnonymous')->willReturn(true);
        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('user')->willReturn($user);
        $container->expects(self::once())->method('language')->willReturn($language);

        $instance = new ApprovedContacts($container);

        $this->assertEquals([], $instance->getInitialUserSet());
    }

    /**
     * @depends testConstruct
     */
    public function testIsHighlighted(ApprovedContacts $instance) : void
    {
        $this->assertFalse($instance->isHighlighted());
    }

    private function expectTranslation(string $method, string $languageKey) : void
    {
        $expected = 'translated: ' . $languageKey;
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $language->expects(self::once())->method('loadLanguageModule')->with('contact');
        $language->expects(self::once())->method('txt')->with($languageKey)->willReturn($expected);

        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('user')->willReturn($user);
        $container->expects(self::once())->method('language')->willReturn($language);

        $instance = new ApprovedContacts($container);
        $this->assertEquals($expected, $instance->$method());
    }
}
