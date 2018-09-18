<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Central entry point for users of the service.
 */
final class ilKioskModeService {

	protected $ctrl;
	protected $language;
	protected $access;
	protected $obj_definition;

	public function __construct(
		ilCtrl $ctrl,
		illanguage $language,
		ilAccess $access,
		ilObjectDefinition $obj_definition
	) {
		$this->ctrl = $ctrl;
		$this->language = $language;
		$this->access = $access;
		$this->obj_definition = $obj_definition;
	}

	/**
	 * Try to get a kiosk mode view for the given object.
	 *
	 * @return	ilKioskModeView|null
	 */
	public function getViewFor(\ilObject $object) {
		$object_type = $object->getType();
		if(! $this->hasKioskMode($object_type))	{
			return null;
		}

		list($location, $class_name) = $this->getClassLocationForType($object_type);
		return new $class_name(
			$object,
			$this->ctrl,
			$this->language,
			$this->access
		);
	}

	/**
	 * Check if objects of a certain type provide kiosk modes in general.
	 *
	 * @param	string	$object_type	needs to be a valid object type
	 */
	public function hasKioskMode(string $object_type) : bool {
		list($location, $class_name) = $this->getClassLocationForType($object_type);
		$class_path = sprintf(
			'%s/../../../%s/class.%s.php',
			__DIR__, $location, $class_name
		);
		return file_exists($class_path);
	}

	/**
	 * @return string[] with [0] => path and [1] => class name
	 */
	protected function getClassLocationForType(string $object_type): array
	{
		$class = $this->obj_definition->getClassName($object_type);
		$location = $this->obj_definition->getLocation($object_type);
		$full_class = "il".$class."KioskModeView";
		return [$location, $full_class];
	}
}
