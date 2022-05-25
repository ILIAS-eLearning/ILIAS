<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosuresSingleton;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;

class ilServicesGlobalScreenTest extends TestCase
{
    private ?Container $dic_backup = null;
    /**
     * @var ilRbacSystem|\PHPUnit\Framework\MockObject\MockObject
     */
    private ilRbacSystem $rbacsystem_mock;
    /**
     * @var ilObjUser|\PHPUnit\Framework\MockObject\MockObject
     */
    private ilObjUser $user_mock;
    private int $SYSTEM_FOLDER_ID;
    private int $ROOT_FOLDER_ID;
    
    protected function setUp() : void
    {
        global $DIC;
        $this->dic_backup = is_object($DIC) ? clone $DIC : $DIC;
        $DIC = new Container();
        if (!defined('SYSTEM_FOLDER_ID')) {
            define('SYSTEM_FOLDER_ID', 42);
        }
        $this->SYSTEM_FOLDER_ID = SYSTEM_FOLDER_ID;
        if (!defined('ROOT_FOLDER_ID')) {
            define('ROOT_FOLDER_ID', 24);
        }
        $this->ROOT_FOLDER_ID = ROOT_FOLDER_ID;
    }
    
    protected function tearDown() : void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }
    
    public function testAdminAccessTrue() : void
    {
        global $DIC;
        $DIC['rbacsystem'] = $rbac_mock = $this->createMock(ilRbacSystem::class);
        $class = new BasicAccessCheckClosures($DIC);
        
        $rbac_mock->expects($this->once())
                  ->method('checkAccess')
                  ->with('visible', $this->SYSTEM_FOLDER_ID)
                  ->willReturn(true);
        
        $this->assertTrue($class->hasAdministrationAccess()());
        $this->assertTrue(
            $class->hasAdministrationAccess()()
        ); // second call to check caching, see expectation $this->once()
    }
    
    public function testAdminAccessFalse() : void
    {
        global $DIC;
        $DIC['rbacsystem'] = $rbac_mock = $this->createMock(ilRbacSystem::class);
        $class = new BasicAccessCheckClosures($DIC);
        
        $rbac_mock->expects($this->once())
                  ->method('checkAccess')
                  ->with('visible', $this->SYSTEM_FOLDER_ID)
                  ->willReturn(false);
        
        $this->assertFalse($class->hasAdministrationAccess()());
        $this->assertFalse(
            $class->hasAdministrationAccess()()
        ); // second call to check caching, see expectation $this->once()
    }
    
    public function testAdminAcessTrueButWithClosure() : void
    {
        global $DIC;
        $DIC['rbacsystem'] = $rbac_mock = $this->createMock(ilRbacSystem::class);
        $class = new BasicAccessCheckClosures($DIC);
        
        $rbac_mock->expects($this->once())
                  ->method('checkAccess')
                  ->with('visible', $this->SYSTEM_FOLDER_ID)
                  ->willReturn(true);
        
        $closure_returning_false = function () : bool {
            return false;
        };
        
        $this->assertTrue($class->hasAdministrationAccess()());
        $this->assertFalse(
            $class->hasAdministrationAccess($closure_returning_false)()
        );
    }
    
    public function testRepoAccessTrueNotLoggedInPublicSection() : void
    {
        global $DIC;
        
        $DIC['ilUser'] = $user_mock = $this->createMock(ilObjUser::class);
        $DIC['ilSetting'] = $settings_mock = $this->createMock(ilSetting::class);
        $DIC['ilAccess'] = $access_mock = $this->createMock(ilAccessHandler::class);
        
        $class = new BasicAccessCheckClosures($DIC);
        
        $user_mock->expects($this->once())
                  ->method('isAnonymous')
                  ->willReturn(true);
        
        $settings_mock->expects($this->once())
                      ->method('get')
                      ->with('pub_section')
                      ->willReturn('1');
        
        $access_mock->expects($this->once())
                    ->method('checkAccess')
                    ->with('read', '', $this->ROOT_FOLDER_ID)
                    ->willReturn(true);
        
        $this->assertTrue($class->isRepositoryReadable()());
        $this->assertTrue(
            $class->isRepositoryReadable()()
        ); // second call to check caching, see expectation $this->once()
        $this->assertTrue(
            $class->isRepositoryReadable(function () : bool {
                return true;
            })()
        );
        $this->assertFalse(
            $class->isRepositoryReadable(function () : bool {
                return false;
            })()
        );
    }
    
    public function testRepoAccessTrueNotLoggedInNoPublicSection() : void
    {
        global $DIC;
        
        $DIC['ilUser'] = $user_mock = $this->createMock(ilObjUser::class);
        $DIC['ilSetting'] = $settings_mock = $this->createMock(ilSetting::class);
        $DIC['ilAccess'] = $access_mock = $this->createMock(ilAccessHandler::class);
        
        $class = new BasicAccessCheckClosures($DIC);
        
        $user_mock->expects($this->once())
                  ->method('isAnonymous')
                  ->willReturn(true);
        
        $settings_mock->expects($this->once())
                      ->method('get')
                      ->with('pub_section')
                      ->willReturn('0');
        
        $access_mock->expects($this->never())
                    ->method('checkAccess');
        
        $this->assertFalse($class->isRepositoryReadable()());
        $this->assertFalse(
            $class->isRepositoryReadable()()
        ); // second call to check caching, see expectation $this->once()
        $this->assertFalse(
            $class->isRepositoryReadable(function () : bool {
                return true;
            })()
        );
        $this->assertFalse(
            $class->isRepositoryReadable(function () : bool {
                return false;
            })()
        );
    }
    
    public function testRepoAccessTrueLoggedIn() : void
    {
        global $DIC;
        
        $DIC['ilUser'] = $user_mock = $this->createMock(ilObjUser::class);
        $DIC['ilSetting'] = $settings_mock = $this->createMock(ilSetting::class);
        $DIC['ilAccess'] = $access_mock = $this->createMock(ilAccessHandler::class);
        
        $class = new BasicAccessCheckClosures($DIC);
        
        $user_mock->expects($this->once())
                  ->method('isAnonymous')
                  ->willReturn(false);
        
        $user_mock->expects($this->once())
                  ->method('getId')
                  ->willReturn(6);
        
        $settings_mock->expects($this->never())
                      ->method('get');
        
        $access_mock->expects($this->once())
                    ->method('checkAccess')
                    ->with('read', '', $this->ROOT_FOLDER_ID)
                    ->willReturn(true);
        
        $this->assertTrue($class->isRepositoryReadable()());
        $this->assertTrue(
            $class->isRepositoryReadable()()
        ); // second call to check caching, see expectation $this->once()
        $this->assertTrue(
            $class->isRepositoryReadable(function () : bool {
                return true;
            })()
        );
        $this->assertFalse(
            $class->isRepositoryReadable(function () : bool {
                return false;
            })()
        );
    }
    
    public function testRepoAccessFalseLoggedIn() : void
    {
        global $DIC;
        
        $DIC['ilUser'] = $user_mock = $this->createMock(ilObjUser::class);
        $DIC['ilSetting'] = $settings_mock = $this->createMock(ilSetting::class);
        $DIC['ilAccess'] = $access_mock = $this->createMock(ilAccessHandler::class);
        
        $class = new BasicAccessCheckClosures($DIC);
        
        $user_mock->expects($this->once())
                  ->method('isAnonymous')
                  ->willReturn(false);
        
        $user_mock->expects($this->once())
                  ->method('getId')
                  ->willReturn(6);
        
        $settings_mock->expects($this->never())
                      ->method('get');
        
        $access_mock->expects($this->once())
                    ->method('checkAccess')
                    ->with('read', '', $this->ROOT_FOLDER_ID)
                    ->willReturn(false);
        
        $this->assertFalse($class->isRepositoryReadable()());
        $this->assertFalse(
            $class->isRepositoryReadable()()
        ); // second call to check caching, see expectation $this->once()
        $this->assertFalse(
            $class->isRepositoryReadable(function () : bool {
                return true;
            })()
        );
        $this->assertFalse(
            $class->isRepositoryReadable(function () : bool {
                return false;
            })()
        );
    }
}
