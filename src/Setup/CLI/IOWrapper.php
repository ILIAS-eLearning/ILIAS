<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\AdminInteraction;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Wrapper around symfonies input and output facilities to provide just the
 * functionality required for the ILIAS-setup.
 */
class IOWrapper implements AdminInteraction
{
    const LABEL_WIDTH = 75;
    const ELLIPSIS = "...";

    /**
     * @var    InputInterface
     */
    protected $in;

    /**
     * @var OutputInterface
     */
    protected $out;

    /**
     * @var    SymfonyStyle
     */
    protected $style;

    /**
     * @var bool
     */
    protected $last_objective_was_notable = false;

    /**
     * @var string
     */
    protected $last_objective_label = "";

    /**
     * @var bool
     */
    protected $output_in_objective = false;

    public function __construct(InputInterface $in, OutputInterface $out)
    {
        $this->in = $in;
        $this->out = $out;
        $this->style = new SymfonyStyle($in, $out);
    }

    // Implementation of AdminInteraction
    public function startProgress(int $max) : void
    {
        $this->style->progressStart($max);
    }

    public function advanceProgress() : void
    {
        $this->style->progressAdvance();
    }

    public function stopProgress() : void
    {
        $this->style->progressFinish();
    }

    public function inform(string $message) : void
    {
        $this->outputInObjective();
        $this->style->note($message);
    }

    public function confirmExplicit(string $message, string $type_text_to_confirm) : bool
    {
        $this->outputInObjective();
        if (!$this->shouldSayYes()) {
            return $this->style->ask(
                $message,
                null,
                function (string $input) use ($type_text_to_confirm) : bool {
                    return $type_text_to_confirm === $input;
                }
            );
        } else {
            $this->inform("Automatically confirmed:\n\n$message");
            return true;
        }
    }

    public function confirmOrDeny(string $message) : bool
    {
        $this->outputInObjective();
        if (!$this->shouldSayYes()) {
            return $this->style->confirm($message, false);
        } else {
            $this->inform("Automatically confirmed:\n\n$message");
            return true;
        }
    }

    // For CLI-Setup

    public function printLicenseMessage() : void
    {
        if ($this->shouldSayYes() || ($this->in->hasOption("no-interaction") && $this->in->getOption("no-interaction"))) {
            return;
        }
        $this->text(
            "   ILIAS Copyright (C) 1998-2019 ILIAS Open Source e.V. - GPLv3\n\n" .
            "This program comes with ABSOLUTELY NO WARRANTY. This is free software,\n" .
            "and you are welcome to redistribute it under certain conditions. Look\n" .
            "into the LICENSE file for details."
        );
    }

    protected function shouldSayYes() : bool
    {
        return $this->in->getOption("yes") ?? false;
    }

    public function title(string $title) : void
    {
        $this->style->title($title);
    }

    public function success(string $text) : void
    {
        $this->style->success($text);
    }

    public function error(string $text) : void
    {
        $this->style->error($text);
    }

    public function text(string $text) : void
    {
        $this->style->text($text);
    }

    public function startObjective(string $label, bool $is_notable)
    {
        $this->last_objective_was_notable = $is_notable;
        $this->last_objective_label = $label;
        $this->output_in_objective = false;
        if ($this->showLastObjectiveLabel()) {
            $this->style->write(str_pad($label . "...", self::LABEL_WIDTH));
        }
    }

    public function finishedLastObjective()
    {
        if ($this->output_in_objective) {
            $this->startObjective($this->last_objective_label, $this->last_objective_was_notable);
        }

        if ($this->showLastObjectiveLabel()) {
            $this->style->write("[<fg=green>OK</>]\n");
        }
    }

    public function failedLastObjective()
    {
        // Always show label of failed objectives.
        if ($this->output_in_objective || !$this->last_objective_was_notable) {
            $this->startObjective($this->last_objective_label, true);
        }

        if ($this->showLastObjectiveLabel()) {
            $this->style->write("[<fg=red>FAILED</>]\n");
        }
    }

    protected function outputInObjective() : void
    {
        if (!$this->output_in_objective && $this->showLastObjectiveLabel()) {
            $this->output_in_objective = true;
            $this->style->write("[in progress]\n");
        }
    }

    protected function showLastObjectiveLabel()
    {
        return $this->last_objective_was_notable
            || $this->out->isVeryVerbose()
            || $this->out->isDebug();
    }

    public function isVerbose() : bool
    {
        return $this->out->isVerbose();
    }
}
