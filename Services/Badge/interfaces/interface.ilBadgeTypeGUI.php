<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Badge type gui interface
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ingroup ServicesBadge
 */
interface ilBadgeTypeGUI
{
	/**
	 * Add custom fields to form
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */
	public function initConfigForm(ilPropertyFormGUI $a_form);
	
	/**
	 * Set form values
	 * 
	 * @param ilPropertyFormGUI $a_form
	 * @param array $a_config
	 */
	public function importConfigToForm(ilPropertyFormGUI $a_form, array $a_config);
	
	/**
	 * Export values to DB
	 * 
	 * @param ilPropertyFormGUI $a_form
	 * @return array
	 */
	public function getConfigFromForm(ilPropertyFormGUI $a_form);	
}