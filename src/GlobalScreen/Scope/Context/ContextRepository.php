<?php namespace ILIAS\GlobalScreen\Scope\Context;

/**
 * Class ContextRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextRepository {

	const C_ORGU_TREE = 'orgu_tree';
	const C_MAIN = 'main';
	const C_DESKTOP = 'desktop';
	const C_REPO = 'repo';
	const C_REPO_TREE = 'repo_tree';


	/**
	 * @return ContextInterface
	 */
	public function main(): ContextInterface {
		return new BasicContext(self::C_MAIN);
	}


	/**
	 * @return ContextInterface
	 */
	public function desktop(): ContextInterface {
		return new BasicContext(self::C_DESKTOP);
	}


	/**
	 * @return ContextInterface
	 */
	public function repository(): ContextInterface {
		return new BasicContext(self::C_REPO);
	}


	/**
	 * @return ContextInterface
	 */
	public function repositoryTree(): ContextInterface {
		return new BasicContext(self::C_REPO_TREE);
	}


	/**
	 * @return ContextInterface
	 */
	public function organizationalUnitsTree(): ContextInterface {
		return new BasicContext(self::C_ORGU_TREE);
	}
}
