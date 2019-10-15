<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\ConfirmationRequester;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Wrapper around symfonies input and output facilities to provide just the
 * functionality required for the ILIAS-setup.
 */
class IOWrapper implements ConfirmationRequester {
	const LABEL_WIDTH = 75;
	const ELLIPSIS = "...";

	/**
	 * @var	InputInterface
	 */
	protected $in;

	/**
	 * @var OutputInterface
	 */
	protected $out;

	/**
	 * @var	SymfonyStyle
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

	public function __construct(InputInterface $in, OutputInterface $out) {
		$this->in = $in;
		$this->out = $out;
		$this->style = new SymfonyStyle($in, $out);
	}

	public function startObjective(string $label, bool $is_notable) {
		$this->last_objective_was_notable = $is_notable;
		$this->last_objective_label = $label;
		$this->output_in_objective = false;
		if ($this->showLastObjectiveLabel()) {
			$this->style->write(str_pad($label."...", self::LABEL_WIDTH));
		}
	}

	public function finishedLastObjective() {
		if ($this->output_in_objective) {
			$this->startObjective($this->last_objective_label, $this->last_objective_was_notable);
		}

		if ($this->showLastObjectiveLabel()) {
			$this->style->write("[<fg=green>OK</>]\n");
		}
	}

	public function confirmOrDeny(string $message) : bool {
		$this->outputInObjective();
		return $this->style->confirm($message, false);
	}

	protected function outputInObjective() : void {
		if (!$this->output_in_objective && $this->showLastObjectiveLabel()) {
			$this->output_in_objective = true;
			$this->style->write("[in progress]\n");
		}
	}

	protected function showLastObjectiveLabel() {
		return $this->last_objective_was_notable
			|| $this->out->isVeryVerbose()
			|| $this->out->isDebug();
	}
}
