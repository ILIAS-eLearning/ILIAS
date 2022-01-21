<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\ObjectiveIterator;
use ILIAS\Setup\UnachievableException;

/**
 * Add this to an Command that has wants to achieve objectives.
 */
trait ObjectiveHelper
{
    protected function achieveObjective(
        Objective $objective,
        Environment $environment,
        IOWrapper $io = null
    ) : Environment {
        $iterator = new ObjectiveIterator($environment, $objective);

        while ($iterator->valid()) {
            $current = $iterator->current();
            if (!$current->isApplicable($environment)) {
                $iterator->next();
                continue;
            }
            if ($io) {
                $io->startObjective($current->getLabel(), $current->isNotable());
            }
            try {
                $environment = $current->achieve($environment);
                if ($io) {
                    $io->finishedLastObjective();
                }
                $iterator->setEnvironment($environment);
            } catch (\Throwable $e) {
                $iterator->markAsFailed($current);
                if ($io) {
                    $message = $e->getMessage();
                    $io->failedLastObjective();
                    if ($io->isVerbose()) {
                        $message .= "\n" . debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                    }
                    $io->error($message);
                }
            }
            $iterator->next();
        }

        return $environment;
    }
}
