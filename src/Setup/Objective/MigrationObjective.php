<?php

namespace ILIAS\Setup\Objective;

use ILIAS\Setup;

/**
 * handles a Migration
 */
class MigrationObjective implements Setup\Objective
{
    /**
     * @var Setup\Migration
     */
    protected $migration;
    /**
     * @var int
     */
    protected $steps;

    /**
     * MigrationObjective constructor.
     * @param Setup\Migration $migration
     */
    public function __construct(Setup\Migration $migration, int $steps = null)
    {
        $this->migration = $migration;
        $this->steps = $steps ?? $migration->getDefaultAmountOfStepsPerRun();
    }

    /**
     * Uses hashed Path.
     * @inheritdocs
     */
    public function getHash() : string
    {
        return hash("sha256", self::class . '' . get_class($this->migration));
    }

    /**
     * @inheritdocs
     */
    public function getLabel() : string
    {
        return $this->migration->getLabel();
    }

    /**
     * Defaults to 'true'.
     * @inheritdocs
     */
    public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritdocs
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return $this->migration->getPreconditions($environment);
    }

    /**
     * @inheritdocs
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        /**
         * @var $io Setup\CLI\IOWrapper
         */
        $io = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);
        $key = (new \ReflectionClass($this->migration))->getShortName();
        $confirmation = $io->confirmExplicit(
            "Do you really want to run the following migration? Make sure you have a backup\n" .
            "of all your data. You will run this migration on your own risk.\n\n" .
            "Please type '$key' to confirm and start.",
            $key
        );
        if (!$confirmation) {
            $io->error("Migration '$key' aborted.");
            return $environment;
        }
        $io->inform("Preparing Migration: This may take quite a long time (e.g. all files are collected.");
        $this->migration->prepare($environment);
        $io->inform("Preparing Migration: done.");

        $steps = $this->steps;
        if ($steps === Setup\Migration::INFINITE) {
            $steps = $this->migration->getRemainingAmountOfSteps();
        }
        if ($this->migration->getRemainingAmountOfSteps() < $steps) {
            $steps = $this->migration->getRemainingAmountOfSteps();
        }
        $io->inform("Trigger {$steps} step(s) in {$this->getLabel()}");
        $step = 0;
        $io->startProgress($steps);

        while ($step < $steps) {
            $io->advanceProgress();
            $this->migration->step($environment);
            $step++;
        }
        $io->stopProgress();
        $remaining = $this->migration->getRemainingAmountOfSteps();
        $io->inform("{$remaining} step(s) remaining. Run again to proceed.");

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $this->migration->prepare($environment);

        return $this->migration->getRemainingAmountOfSteps() > 0;
    }
}
