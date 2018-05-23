<?php
namespace ILIAS\DI;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class DIContainerTest
 */
class DIContainerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Container
     */
    protected $DIC;

    protected function setUp() {
        $this->DIC = new Container();
    }

    public function testIsDependencyAvailableIfNotAvailable(){
        $this->assertFalse($this->DIC->isDependencyAvailable("notAvailable"));
    }

    public function testIsDependencyAvailableIfAvailable(){
        $DIC["available"] = function (){};
        $this->assertTrue($this->DIC->isDependencyAvailable("available"));
    }
}
