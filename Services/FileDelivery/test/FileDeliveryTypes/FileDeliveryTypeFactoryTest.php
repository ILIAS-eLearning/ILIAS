<?php

namespace ILIAS\FileDelivery\FileDeliveryTypes;

require_once('./libs/composer/vendor/autoload.php');
require_once './Services/FileDelivery/classes/FileDeliveryTypes/PHP.php';
require_once './Services/FileDelivery/classes/FileDeliveryTypes/PHPChunked.php';
require_once './Services/FileDelivery/classes/FileDeliveryTypes/FileDeliveryTypeFactory.php';
require_once './Services/FileDelivery/classes/FileDeliveryTypes/DeliveryMethod.php';
require_once './Services/Exceptions/classes/class.ilException.php';

use ilException;
use ILIAS\HTTP\GlobalHttpState;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class FileDeliveryTypeFactoryTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runInSeparateProcess
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class FileDeliveryTypeFactoryTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
    /**
     * @var GlobalHttpState|MockInterface $http
     */
    private $http;
    /**
     * @var FileDeliveryTypeFactory $subject
     */
    private $subject;

    protected function setUp()
    {
        parent::setUp();

        $this->http = Mockery::mock(GlobalHttpState::class);

        //the factory should not interact with the service.
        $this->http->shouldNotReceive();

        $this->subject = new FileDeliveryTypeFactory($this->http);
    }


    /**
     * @Test
     */
    public function testCreatePHPFileDeliveryWhichShouldSucceed()
    {
        $result = $this->subject->getInstance(DeliveryMethod::PHP);

        $this->assertInstanceOf(PHP::class, $result);
    }

    /**
     * @Test
     */
    public function testCreatePHPChunkedFileDeliveryWhichShouldSucceed()
    {
        $result = $this->subject->getInstance(DeliveryMethod::PHP_CHUNKED);

        $this->assertInstanceOf(PHPChunked::class, $result);
    }


    /**
     * @Test
     */
    public function testCreatePHPFileDeliveryTypeWhichShouldYieldTheSameInstance()
    {

        //fetch the php file delivery type two times to check that only one instance is created.
        $firstResult = $this->subject->getInstance(DeliveryMethod::PHP);
        $secondResult = $this->subject->getInstance(DeliveryMethod::PHP);

        $this->assertEquals($firstResult, $secondResult);
    }

    /**
     * @Test
     */
    public function testCreateAnUnknownFileDeliveryTypeWhichShouldFail()
    {

        //get instance should throw an exception if the file delivery type is not known.
        $type = 'unknown file delivery type';
        $this->setExpectedException(ilException::class, "Unknown file delivery type \"$type\"");

        $this->subject->getInstance('unknown file delivery type');
    }
}
