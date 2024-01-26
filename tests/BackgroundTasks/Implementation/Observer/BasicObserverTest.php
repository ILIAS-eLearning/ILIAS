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
        BasicPersistence::instance($this->createMock(\ilDBInterface::class))->saveBucketAndItsTasks($observer);
    }
}
