<?php
namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Table\Data\Factory\Factory as DataTableFactory;
use ILIAS\UI\Component\Table\Data\Factory\Factory as DataTableFactoryInterface;

/**
 * Implementation of factory for tables
 *
 * @author Nils Haagen <nhaageng@concepts-and-training.de>
 */
class Factory implements T\Factory {

	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;

	/**
	 * @param SignalGeneratorInterface $signal_generator
	 */
	public function __construct(SignalGeneratorInterface $signal_generator) {
		$this->signal_generator = $signal_generator;
	}

	/**
	 * @inheritdoc
	 */
	public function presentation($title, array $view_controls, \Closure $row_mapping) {
		return new Presentation($title, $view_controls, $row_mapping, $this->signal_generator);
	}


	/**
	 * @inheritDoc
	 */
	public function data(Container $dic): DataTableFactoryInterface {
		return new DataTableFactory($dic);
	}
}
