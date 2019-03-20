<?php namespace ILIAS\NavigationContext;

/**
 * Class ContextRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextRepository {

	const C_MAIN = 'main';
	const C_DESKTOP = 'desktop';
	const C_REPO = 'repo';
	const C_ADMINISTRATION = 'administration';
	const C_MAIL = 'mail';


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
	public function mail(): ContextInterface {
		return new BasicContext(self::C_MAIL);
	}


	/**
	 * @return ContextInterface
	 */
	public function administration(): ContextInterface {
		return new BasicContext(self::C_ADMINISTRATION);
	}
}
