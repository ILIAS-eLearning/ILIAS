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
            } catch (UnachievableException $e) {
                $iterator->markAsFailed($current);
                if ($io) {
                    $io->error($e->getMessage());
                    $io->failedLastObjective();
                }
            }
            $iterator->next();
        }

        return $environment;
    }
}
