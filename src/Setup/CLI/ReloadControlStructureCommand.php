<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Config;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Objective\ObjectiveWithPreconditions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reload Control Structure command.
 */
class ReloadControlStructureCommand extends Command
{
    use ObjectiveHelper;

    protected static $defaultName = "reload-control-structure";

    /**
     * var Objective[]
     */
    protected $preconditions;

    /**
     * @var Objective[] $preconditions will be achieved before command invocation
     */
    public function __construct(array $preconditions)
    {
        parent::__construct();
        $this->preconditions = $preconditions;
    }


    public function configure()
    {
        $this->setDescription("Reloads the control structure of the installation.");
        $this->addOption("yes", "y", InputOption::VALUE_NONE, "Confirm every message of the update.");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new IOWrapper($input, $output);
        $io->printLicenseMessage();
        $io->title("Reload Control Structure of ILIAS");

        // ATTENTION: This is not how we want to do this in general during the
        // setup, stuff should use Dependency Injection. However, since we
        // currently won't get there with the control structure but still need
        // a quick way to reload it, we do it anyway.
        //
        // Also, there probably will be more commands that Agents of components
        // will want to offer (e.g. flush global cache). So we should think of
        // some general mechanism some time.
        $objective = new ObjectiveCollection(
            "Reload Control Structure of ILIAS",
            false,
            new \ilCtrlStructureStoredObjective(
                new \ilCtrlStructureReader()
            ),
            new \ilComponentDefinitionsStoredObjective(false)
        );
        $objective = $this->getUpdateObjective($config);
        if (count($this->preconditions) > 0) {
            $objective = new ObjectiveWithPreconditions(
                $objective,
                ...$this->preconditions
            );
        }

        $environment = new ArrayEnvironment([
            Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);

        try {
            $this->achieveObjective($objective, $environment, $io);
            $io->success("Control structure reloaded. Thanks and have fun!");
        } catch (NoConfirmationException $e) {
            $io->error("Aborting Reload of Control Structure, a necessary confirmation is missing:\n\n" . $e->getRequestedConfirmation());
        }
    }
}
