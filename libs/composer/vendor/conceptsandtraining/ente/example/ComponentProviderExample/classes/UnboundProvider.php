<?php

namespace CaT\Plugins\ComponentProviderExample;

require_once(__DIR__."/../vendor/autoload.php");

use \CaT\Ente\ILIAS\SeparatedUnboundProvider as Base;
use \CaT\Ente\Simple\AttachString;
use \CaT\Ente\Simple\AttachStringMemory;
use \CaT\Ente\ILIAS\Entity;

class UnboundProvider extends Base {
    /**
     * @inheritdocs
     */
    public function componentTypes() {
        return [AttachString::class];
    }

    /**
     * Build the component(s) of the given type for the given object.
     *
     * @param   string    $component_type
     * @param   Entity    $provider
     * @return  Component[]
     */
    public function buildComponentsOf($component_type, Entity $entity) {
        if ($component_type === AttachString::class) {
            $returns = [];
			foreach ($this->owner()->getProvidedStrings() as $s) {
				$returns[] = new AttachStringMemory($entity, $s);
			}
            return $returns;
        }
        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }
}
