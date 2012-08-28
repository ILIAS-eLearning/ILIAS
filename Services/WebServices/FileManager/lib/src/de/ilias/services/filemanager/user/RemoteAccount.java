/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.user;

import de.ilias.services.filemanager.soap.api.SoapClientUser;

/**
 * Class RemoteAccount
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class RemoteAccount {
	
	private static RemoteAccount account = null;
	
	private SoapClientUser user;
	
	private RemoteAccount(SoapClientUser user) {
		
		this.user = user;
		
	}
	
	/**
	 * Get Instance
	 * @return 
	 */
	public static RemoteAccount getInstance() {
		
		return account;
	}
	
	/**
	 * Create new instance
	 * @param user
	 * @return 
	 */
	public static RemoteAccount newInstance(SoapClientUser user) {
		
		account = new RemoteAccount(user);
		return account;
	}

	/**
	 * Get user
	 * @return 
	 */
	public SoapClientUser getUser() {
		return this.user;
	}
}
