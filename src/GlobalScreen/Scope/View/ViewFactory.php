<?php namespace ILIAS\GlobalScreen\Scope\View;

use ILIAS\UI\Component\Layout\Page\Factory;
use ILIAS\UI\NotImplementedException;

/**
 * Class ViewFactory
 *
 * @package ILIAS\GlobalScreen\Scope\View
 */
class ViewFactory {

	/**
	 * @var array
	 */
	private static $views = [];
	/**
	 * @var Factory
	 */
	private $page_factory;


	/**
	 * ViewFactory constructor.
	 *
	 * @param Factory $page_factory
	 */
	public function __construct(Factory $page_factory) {
		$this->page_factory = $page_factory;
	}


	/**
	 * @return View
	 */
	public function standardView(): View {
		return $this->get(StandardView::class);
	}


	/**
	 * @return View
	 * @throws NotImplementedException
	 */
	public function printView(): View {
		throw new NotImplementedException();
	}


	/**
	 * @param string $class_name
	 *
	 * @return mixed
	 */
	private function get(string $class_name) {
		if (!isset(self::$views[$class_name])) {
			self::$views[$class_name] = new $class_name($this->page_factory);
		}

		return self::$views[$class_name];
	}
}
