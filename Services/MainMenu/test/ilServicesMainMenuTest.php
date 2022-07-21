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

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;
use ILIAS\MainMenu\Provider\CustomMainBarProvider;
use ILIAS\GlobalScreen\Services;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\LinkItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\GlobalScreen\MainMenu\IdentificationTest;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\TypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopLinkItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\TopParentItemRenderer;
use ILIAS\UI\Component\MainControls\Slate\Combined;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\LinkList;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\LinkListItemRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Separator;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\SeparatorItemRenderer;
use ILIAS\UI\Component\Divider\Horizontal;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\RepositoryLink;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

class ilServicesMainMenuTest extends TestCase
{
    private ?\ILIAS\DI\Container $dic_backup;
    /**
     * @var ilDBInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected ilDBInterface $db_mock;
    protected Container $dic_mock;
    
    protected function setUp() : void
    {
        global $DIC;
        if (!defined('ILIAS_HTTP_PATH')) {
            define('ILIAS_HTTP_PATH', 'https://ilias.de/');
        }
        if (!defined('CLIENT_ID')) {
            define('CLIENT_ID', 'client');
        }
        if (!defined('SYSTEM_FOLDER_ID')) {
            define('SYSTEM_FOLDER_ID', 0);
        }
        $this->dic_backup = is_object($DIC) ? clone $DIC : $DIC;
        $this->dic_mock = $DIC = new Container();
        $this->provider_factory_mock = $this->createMock(ProviderFactory::class);
        $this->gs_mock = $DIC['global_screen'] = new Services($this->provider_factory_mock);
        $this->db_mock = $DIC['ilDB'] = $this->createMock(ilDBInterface::class);
        $this->dic_mock['ilUser'] = $DIC['ilUser'] = $this->createMock(ilObjUser::class);
        $this->dic_mock['ilSetting'] = $DIC['ilSetting'] = $this->createMock(ilSetting::class);
        $this->dic_mock['rbacsystem'] = $DIC['rbacsystem'] = $this->createMock(ilRbacSystem::class);
        $this->dic_mock['lng'] = $DIC['lng'] = $this->createMock(ilLanguage::class);
        $this->dic_mock['ui.factory'] = $DIC['ui.factory'] = $this->createMock(\ILIAS\UI\Factory::class);
        $this->dic_mock['ui.renderer'] = $DIC['ui.renderer'] = $this->createMock(\ILIAS\UI\Renderer::class);
        $this->dic_mock['objDefinition'] = $DIC['objDefinition'] = $this->createMock(ilObjectDefinition::class);
    }
    
    protected function tearDown() : void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }
    
    /** @noinspection PhpArrayIndexImmediatelyRewrittenInspection */
    public function testTypeHandlers() : void
    {
        $provider = new CustomMainBarProvider($this->dic_mock, $this->createMock(ilMainMenuAccess::class));
        $type_info = $provider->provideTypeInformation();
        $this->assertInstanceOf(TypeInformationCollection::class, $type_info);
        
        // TopLink Item
        $item_type_info = $type_info->get(TopParentItem::class);
        $renderer = $item_type_info->getRenderer();
        $this->assertInstanceOf(TopParentItemRenderer::class, $renderer);
        $this->assertInstanceOf(
            Combined::class,
            $renderer->getComponentForItem(new TopParentItem(new NullIdentification()))
        );
        
        // Link Item
        $item_type_info = $type_info->get(Link::class);
        $renderer = $item_type_info->getRenderer();
        $this->assertInstanceOf(ilMMLinkItemRenderer::class, $renderer);
        $this->assertInstanceOf(
            \ILIAS\UI\Component\Link\Link::class,
            $renderer->getComponentForItem(new Link(new NullIdentification()))
        );
        
        // LinkList Item
        $item_type_info = $type_info->get(LinkList::class);
        $renderer = $item_type_info->getRenderer();
        $this->assertInstanceOf(LinkListItemRenderer::class, $renderer);
        $this->assertInstanceOf(
            Combined::class,
            $renderer->getComponentForItem(new LinkList(new NullIdentification()))
        );
        
        // Separator Item
        $item_type_info = $type_info->get(Separator::class);
        $renderer = $item_type_info->getRenderer();
        $this->assertInstanceOf(SeparatorItemRenderer::class, $renderer);
        $this->assertInstanceOf(
            Horizontal::class,
            $renderer->getComponentForItem(new Separator(new NullIdentification()))
        );
        
        // RepositoryLink Item
        $this->dic_mock['ilObjDataCache'] = $this->createMock(ilObjectDataCache::class);
        $item_type_info = $type_info->get(RepositoryLink::class);
        $renderer = $item_type_info->getRenderer();
        $this->assertInstanceOf(ilMMRepositoryLinkItemRenderer::class, $renderer);
        $this->assertInstanceOf(
            \ILIAS\UI\Component\Link\Link::class,
            $renderer->getComponentForItem(new RepositoryLink(new NullIdentification()))
        );
    }
    
    public function testStandardTopItems() : void
    {
        $this->dic_mock['lng'] = $this->createMock(ilLanguage::class);
        $standard_top_items = new StandardTopItemsProvider($this->dic_mock);
        $items = $standard_top_items->getStaticTopItems();
        $item_identifications = array_map(
            fn (isItem $i) : IdentificationInterface => $i->getProviderIdentification(),
            $items
        );

        $this->assertEquals(7, count($items)); // this contains Dashboard as well
        $this->assertEquals(7, count($item_identifications));
        
        $repo = $standard_top_items->getRepositoryIdentification();
        $this->assertTrue(in_array($repo, $item_identifications));
        
        $admin = $standard_top_items->getAdministrationIdentification();
        $this->assertTrue(in_array($admin, $item_identifications));
        
        $achievments = $standard_top_items->getAchievementsIdentification();
        $this->assertTrue(in_array($achievments, $item_identifications));
        
        $communication = $standard_top_items->getCommunicationIdentification();
        $this->assertTrue(in_array($communication, $item_identifications));
        
        $organisation = $standard_top_items->getOrganisationIdentification();
        $this->assertTrue(in_array($communication, $item_identifications));
        
        $personal = $standard_top_items->getPersonalWorkspaceIdentification();
        $this->assertTrue(in_array($personal, $item_identifications));

        
//        $this->assertFalse(true);
    }
}
