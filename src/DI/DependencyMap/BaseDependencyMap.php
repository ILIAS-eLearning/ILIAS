<?php

namespace ILIAS\DI\DependencyMap;

use ILIAS\DI\Container;

/**
 * Class BaseDependencyMap
 *
 * @package ILIAS\DI
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
class BaseDependencyMap extends EmptyDependencyMap {

	public function __construct() {
		$this->maps = [[$this, 'resolveBaseDependencies']];
	}


	protected function resolveBaseDependencies(Container $DIC, string $fullyQualifiedDomainName, string $for) {
		// wow, why a switch statement and not an array?
		// because we don't really want type unsafe array access on $DIC.
		switch ($fullyQualifiedDomainName) {
			case \ilDBInterface::class:
				return $DIC->database();
			case \ilDB::class:
				return $DIC->database();
			case \ilRbacAdmin::class:
				return $DIC->rbac()->admin();
			case \ilRbacReview::class:
				return $DIC->rbac()->review();
			case \ilRbacSystem::class:
				return $DIC->rbac()->system();
			case \ilAccessHandler::class:
				return $DIC->access();
			case \ilCtrl::class:
				return $DIC->ctrl();
			case \ilObjUser::class:
				return $DIC->user();
			case \ilTree::class:
				return $DIC->repositoryTree();
			case \ilLanguage::class:
				return $DIC->language();
			case \ilLoggerFactory::class:
				return $DIC["ilLoggerFactory"];
			case \ilLogger::class:
				return $DIC->logger()->root();
			case \ilToolbarGUI::class:
				return $DIC->toolbar();
			case \ilTabsGUI::class:
				return $DIC->tabs();
			case Injector::class:
				return $DIC->injector();
			case \ilSetting::class:
				return $DIC->settings();
			case \ILIAS\UI\Factory::class:
				return $DIC->ui()->factory();
			case \ILIAS\UI\Renderer::class:
				return $DIC->ui()->renderer();
			case \ilTemplate::class:
				return $DIC->ui()->mainTemplate();
		}
	}
}