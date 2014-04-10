<?php

/**
 * Class ilShibbolethAuthenticationPluginInt
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilShibbolethAuthenticationPluginInt {

	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function beforeLogin(ilObjUser $user);


	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function afterLogin(ilObjUser $user);


	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function beforeLogout(ilObjUser $user);


	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function afterLogout(ilObjUser $user);


	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function beforeCreateUser(ilObjUser $user);


	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function afterCreateUser(ilObjUser $user);


	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function beforeUpdateUser(ilObjUser $user);


	/**
	 * @param ilObjUser $user
	 *
	 * @return ilObjUser
	 */
	public function afterUpdateUser(ilObjUser $user);
}

?>
