<?php
namespace ILIAS\DI;

use PHPUnit\Framework\TestCase;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class DIContainerTest
 */
class DIContainerTest extends TestCase {

    /**
     * @var Container
     */
    protected $DIC;

    protected function setUp(): void{
        $this->DIC = new Container();
    }

    public function testIsDependencyAvailableIfNotAvailable(){
        $this->assertFalse($this->DIC->isDependencyAvailable("ctrl"));
    }

    public function testIsDependencyAvailableIfAvailable(){
        $this->DIC["ilCtrl"] = function (){};
        $this->assertTrue($this->DIC->isDependencyAvailable("ctrl"));
    }
}
