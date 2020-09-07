<?php
/**
 * Created by PhpStorm.
 * User: otruffer
 * Date: 20.04.17
 * Time: 16:50
 */

namespace BackgroundTasks\Implementation\Observer;

use ILIAS\BackgroundTasks\Exceptions\Exception;
use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Implementation\Persistence\BasicPersistence;
use ILIAS\BackgroundTasks\Implementation\Tasks\DownloadInteger;
use Mockery\Adapter\Phpunit\MockeryTestCase;

require_once("libs/composer/vendor/autoload.php");

class BasicObserverTest extends MockeryTestCase
{
    public function testCheckIntegrity()
    {
        $this->expectException(Exception::class);

        $observer = new BasicBucket();
        $observer->setTask(new DownloadInteger());
        BasicPersistence::instance()->saveBucketAndItsTasks($observer);
    }
}
