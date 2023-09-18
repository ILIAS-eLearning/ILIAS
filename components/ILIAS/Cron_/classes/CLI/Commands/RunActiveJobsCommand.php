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

namespace ILIAS\Cron\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ilCronStartUp;
use Exception;
use ilStrictCliCronManager;

class RunActiveJobsCommand extends Command
{
    protected static $defaultName = 'run-jobs';

    /** @var OutputInterface|StyleInterface */
    private $style;

    protected function configure(): void
    {
        $this->setDescription('Runs cron jobs depending on the respective schedule');

        $this->addArgument('user', InputArgument::REQUIRED, 'The ILIAS user the script is executed with');
        $this->addArgument('client_id', InputArgument::REQUIRED, 'The ILIAS client_id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->style = new SymfonyStyle($input, $output);

        $cron = new ilCronStartUp(
            $input->getArgument('client_id'),
            $input->getArgument('user')
        );

        try {
            $cron->authenticate();

            $this->withAuthenticated($input, $output);

            $this->style->success('Success');

            return 0;
        } catch (Exception $e) {
            $this->style->error($e->getMessage());
            $this->style->error($e->getTraceAsString());

            return 1;
        } finally {
            $cron->logout();
        }
    }

    private function withAuthenticated(InputInterface $input, OutputInterface $output): void
    {
        global $DIC;

        $strictCronManager = new ilStrictCliCronManager(
            $DIC->cron()->manager()
        );
        $strictCronManager->runActiveJobs($DIC->user());
    }
}
