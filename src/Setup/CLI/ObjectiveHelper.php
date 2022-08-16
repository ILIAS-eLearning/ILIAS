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
 */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\ObjectiveIterator;
use ILIAS\Setup\NotExecutableException;

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
        $current = null;

        register_shutdown_function(static function () use (&$current) {
            if (null !== $current) {
                throw new \RuntimeException("Objective '{$current->getLabel()}' failed because it halted the program.");
            }
        });

        while ($iterator->valid()) {
            $current = $iterator->current();
            if (!$current->isApplicable($environment)) {
                // reset objective to mark it as processed without halting the program.
                $current = null;
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
            } catch (NotExecutableException $e) {
                throw $e;
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
            } finally {
                // reset objective to mark it as processed without halting the program.
                $current = null;
            }
            $iterator->next();
        }

        return $environment;
    }
}
