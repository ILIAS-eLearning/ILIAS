<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Central entry point for users of the service.
 */
final class ilKioskModeService
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $language;

    /**
     * @var ilAccess
     */
    protected $access;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    public function __construct(
        ilCtrl $ctrl,
        ilLanguage $language,
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
    public function getViewFor(\ilObject $object)
    {
        $object_type = $object->getType();
        if (!$this->hasKioskMode($object_type)) {
            return null;
        }

        $class_name = $this->getClassNameForType($object_type);

        return new $class_name(
            $object,
            $this->ctrl,
            $this->language,
            $this->access
        );
    }

    /**
     * Check if objects of a certain type provides kiosk mode in general.
     *
     * @param	string	$object_type	needs to be a valid object type
     */
    public function hasKioskMode(string $object_type) : bool
    {
        $class_name = $this->getClassNameForType($object_type);
        return class_exists($class_name);
    }

    /**
     * @return classname of type-specific kiosk view.
     */
    protected function getClassNameForType(string $object_type) : string
    {
        $class = $this->obj_definition->getClassName($object_type);
        $full_class = "il" . $class . "KioskModeView";
        return $full_class;
    }
}
