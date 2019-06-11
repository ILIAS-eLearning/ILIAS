<?php

namespace ILIAS\Messaging\Example\ExampleCourse\Infrastructure\Projection;

class BaseCourseProjection {

	public function project(DomainEvents $eventStream) {
		foreach ($eventStream as $event) {
			$projectMethod = 'project' . $this->short($event);
			$this->$projectMethod($event);
		}
	}

	//TODO extract


	/**
	 * The class name of an object, without the namespace
	 *
	 * @param object|string $object
	 *
	 * @return string
	 */
	function short($object) {
		$parts = explode('\\', $this->fqcn($object));

		return end($parts);
	}


	/**
	 * Fully qualified class name of an object, without a leading backslash
	 *
	 * @param object|string $object
	 *
	 * @return string
	 */
	function fqcn($object) {
		if (is_string($object)) {
			return str_replace('.', '\\', $object);
		}
		if (is_object($object)) {
			return trim(get_class($object), '\\');
		}
		throw new \InvalidArgumentException(sprintf("Expected an object or a string, got %s", gettype($object)));
	}
}



