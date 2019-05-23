<?php namespace ILIAS\GlobalScreen\BootLoader;

/**
 * Class InterfaceFinder
 *
 * @package ILIAS\Collector
 */
class InterfaceFinder extends RegexFinder {

	/**
	 * @var string
	 */
	protected $interface = "";


	/**
	 * InterfaceFinder constructor.
	 *
	 * @param string $interface
	 */
	public function __construct(string $interface, string $regex, string $path) {
		parent::__construct($regex, $path);
		$this->interface = $interface;
	}


	/**
	 * @inheritDoc
	 */
	public function getFiles(): array {
		return array_filter(
			parent::getFiles(), function ($class_name) {
			try {
				$implements_interface = false;
				$r = new \ReflectionClass($class_name);
				if ($r->isInstantiable() && !$r->isAbstract()) {
					$implements_interface = $r->implementsInterface($this->interface);
				}
				unset($r);

				return $implements_interface;
			} catch (\Throwable $e) {
				return false;
			}
		}
		);
	}
}
