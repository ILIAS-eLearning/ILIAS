<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Authentication plugin
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
abstract class ilAuthPlugin extends ilPlugin implements ilAuthDefinition
{

	/**
	 * @return string Component-name
	 */
	public function getComponentName()
	{
		return 'Authentication';
	}


	/**
	 * @return string
	 */
	public function getComponentType()
	{
		return IL_COMP_SERVICE;
	}


	/**
	 * @return string Slot-Name
	 */
	public function getSlot()
	{
		return 'AuthenticationHook';
	}


	/**
	 * @return string Slot-ID
	 */
	public function getSlotId()
	{
		return 'authhk';
	}


	/**
	 * Special alot Init, currently nothing to do here
	 */
	public final function slotInit()
	{
		//
	}


	/**
	 * Does your AuthProvider needs "ext_account"? return true, false otherwise.
	 *
	 * @param string $a_auth_id
	 *
	 * @return bool
	 */
	abstract public function isExternalAccountNameRequired($a_auth_id);


	/**
	 * @param ilAuthCredentials $credentials
	 * @param string            $a_auth_mode
	 *
	 * @return ilAuthProviderInterface Your special instance of
	 *                                 ilAuthProviderInterface where all the magic
	 *                                 happens. You get the ilAuthCredentials and
	 *                                 the user-selected (Sub-)-Mode as well.
	 */
	abstract public function getProvider(ilAuthCredentials $credentials, $a_auth_mode);


	/**
	 * @param string $a_auth_id
	 *
	 * @return string Text-Representation of your Auth-mode.
	 */
	abstract public function getAuthName($a_auth_id);


	/**
	 * @param $a_auth_id
	 *
	 * @return array return an array with all your sub-modes (options) if you have some.
	 *               The array comes as ['subid1' => 'Name of the Sub-Mode One', ...]
	 *               you can return an empty array if you have just a "Main"-Mode.
	 */
	abstract public function getMultipleAuthModeOptions($a_auth_id);


	/**
	 * @param string $id (can be your Mode or – if you have any – a Sub-mode.
	 *
	 * @return bool
	 */
	abstract public function isAuthActive($id);


	/**
	 * @return array IDs of your Auth-Modes and Sub-Modes.
	 */
	abstract public function getAuthIds();
}
