<?php declare(strict_types=1);

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

        while ($iterator->valid()) {
            $current = $iterator->current();
            if (!$current->isApplicable($environment)) {
                $iterator->next();
                continue;
            }
            if ($io !== null) {
                $io->startObjective($current->getLabel(), $current->isNotable());
            }
            try {
                $environment = $current->achieve($environment);
                if ($io !== null) {
                    $io->finishedLastObjective();
                }
                $iterator->setEnvironment($environment);
            } catch (NotExecutableException $e) {
                throw $e;
            } catch (\Throwable $e) {
                $iterator->markAsFailed($current);
                if ($io !== null) {
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
