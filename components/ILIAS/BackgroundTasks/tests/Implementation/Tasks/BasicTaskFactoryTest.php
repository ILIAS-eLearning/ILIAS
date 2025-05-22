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
 * Date: 12.05.17
 * Time: 10:05
 */

namespace BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Implementation\Tasks\BasicTaskFactory;
use ILIAS\BackgroundTasks\Implementation\Tasks\PlusJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\DI\Container;
use ILIAS\BackgroundTasks\Dependencies\DependencyMap\BaseDependencyMap;
use ILIAS\BackgroundTasks\Dependencies\Injector;
use PHPUnit\Framework\TestCase;

class BasicTaskFactoryTest extends TestCase
{
    public function testBasicFactory(): void
    {
        $dic = new Container();
        $injector = new Injector($dic, new BaseDependencyMap());
        $taskFactory = new BasicTaskFactory($injector);
        $plusJob = $taskFactory->createTask(PlusJob::class, [1, 2]);
        $this->assertTrue($plusJob instanceof PlusJob);
        $plusJobInput = $plusJob->getInput();
        $one = new IntegerValue();
        $one->setValue(1);
        $this->assertTrue($plusJobInput[0]->equals($one));

        $a = new IntegerValue();
        $a->setValue(1);
        $b = new IntegerValue();
        $b->setValue(2);
        $plusJob = $taskFactory->createTask(PlusJob::class, [$a, $b]);
        $this->assertTrue($plusJob instanceof PlusJob);
        $plusJobInput = $plusJob->getInput();
        $this->assertTrue($plusJobInput[0]->equals($one));
    }
}
