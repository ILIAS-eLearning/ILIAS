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

namespace ILIAS;

use ILIAS\Component\Component;
use ILIAS\Component\Resource\PublicAsset;
use ILIAS\Component\Resource\ComponentJS;
use ILIAS\Setup\Agent;
use ILIAS\Refinery\Factory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\ComponentLanguageFileDirectory;
use ILIAS\BackgroundTasks\Registry\Registry;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Implementation\Registry\BasicRegistry;
use ILIAS\BackgroundTasks\Implementation\Values;
use ILIAS\BackgroundTasks\Implementation\Tasks;

class BackgroundTasks implements Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $define[] = Registry::class;

        // components putting tasks or values into buckets contribute them, see Registry\Registry
        $implement[Registry::class] = static fn(): Registry =>
            new BasicRegistry(
                $seek[Task::class],
                $seek[Value::class]
            );

        $contribute[Value::class] = static fn() => new Values\ScalarValues\ScalarValue();
        $contribute[Value::class] = static fn() => new Values\ScalarValues\BooleanValue();
        $contribute[Value::class] = static fn() => new Values\ScalarValues\FloatValue();
        $contribute[Value::class] = static fn() => new Values\ScalarValues\IntegerValue();
        $contribute[Value::class] = static fn() => new Values\ScalarValues\StringValue();
        $contribute[Value::class] = static fn() => new Values\AggregationValues\ListValue();
        $contribute[Value::class] = static fn() => new Values\ThunkValue();
        $contribute[Task::class] = static fn() => new Tasks\Aggregation\ConcatenationJob();
        $contribute[Task::class] = static fn() => new Tasks\NotFoundUserInteraction();

        $contribute[Agent::class] = static fn(): Agent =>
            new \ILIAS\BackgroundTasks\Setup\Agent(
                $pull[Factory::class]
            );
        $contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(
            $this,
            'bgtask'
        );
        $contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(
            $this,
            'background_tasks'
        );

        $contribute[PublicAsset::class] = fn(): ComponentJS => new ComponentJS(
            $this,
            "background_task_refresh.js"
        );
    }
}
