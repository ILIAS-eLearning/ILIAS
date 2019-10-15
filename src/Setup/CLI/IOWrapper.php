<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Wrapper around symfonies input and output facilities to provide just the
 * functionality required for the ILIAS-setup.
 */
class IOWrapper {
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

	public function __construct(InputInterface $in, OutputInterface $out) {
		$this->in = $in;
		$this->out = $out;
		$this->style = new SymfonyStyle($in, $out);
	}

	public function startObjective(string $label, bool $is_notable) {
		$this->last_command_was_notable = $is_notable;
		$this->last_objective_label = $label;
		if ($is_notable || $this->out->isVeryVerbose()  || $this->out->isDebug()) {
			$this->style->write(str_pad($label."...", self::LABEL_WIDTH));
		}
	}

	public function finishedLastObjective() {
		if ($this->last_command_was_notable || $this->out->isVeryVerbose()  || $this->out->isDebug()) {
			$this->style->write("[<fg=green>OK</>]\n");
		}
	}
}
