<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrapper around symfonies input and output facilities to provide just the
 * functionality required for the ILIAS-setup.
 */
class IOWrapper {
	/**
	 * @var	InputInterface
	 */
	protected $in;

	/**
	 * @var OutputInterface
	 */
	protected $out;

	public function __construct(InputInterface $in, OutputInterface $out) {
		$this->in = $in;
		$this->out = $out;
	}

	public function startObjective(string $label, bool $is_notable) {
		if ($is_notable || $this->out->isVeryVerbose()  || $this->out->isDebug()) {
			$this->out->writeln($label);
		}
	}

	public function finishedLastObjective() {
	}
}
