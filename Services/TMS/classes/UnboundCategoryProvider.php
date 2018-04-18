<?php

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Cockpit\CockpitItem;
use \ILIAS\TMS\Cockpit\CockpitItemImpl;

class UnboundCategoryProvider extends SeparatedUnboundProvider {
	/**
	 * @inheritdocs
	 */
	public function componentTypes() {
		return [CockpitItem::class];
	}

	/**
	 * Build the component(s) of the given type for the given object.
	 *
	 * @param   string    $component_type
	 * @param   Entity    $provider
	 * @return  Component[]
	 */
	public function buildComponentsOf($component_type, Entity $entity) {
		assert('is_string($component_type)');
		if ($component_type === CockpitItem::class) {
			return $this->getCockpitItems($entity);
		}
		throw new \InvalidArgumentException("Unexpected component type '$component_type'");
	}

	protected function getCockpitItems(Entity $entity) {
		$ret = [];

		if($this->checkVisible()) {
			$ret[] =
				new CockpitItemImpl(
						$entity,
						$this->owner()->getTitle(),
						$this->owner()->getTitle(),
						ilLink::_getLink($this->owner()->getRefId(), "cat"),
						$this->getIconPath($this->owner()->getId()),
						$this->getIconPath($this->owner()->getId()),
						$this->owner()->getRefId()
				);
		}

		return $ret;
	}

	protected function checkVisible() {
		global $DIC;
		$access = $DIC->access();

		return $access->checkAccess("visible", "", $this->owner()->getRefId());
	}

	protected function getIconPath($obj_id) {
		return ilObject::_getIcon($obj_id, "big", "cat", false);
	}
}
