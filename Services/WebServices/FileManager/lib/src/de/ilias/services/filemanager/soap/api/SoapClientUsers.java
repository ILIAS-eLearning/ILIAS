/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import java.util.ArrayList;
import java.util.List;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlRootElement;

/**
 * Class SoapClientUsers
 * Represents users.xml description
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlRootElement(name = "Users")
public class SoapClientUsers extends SoapClientResponse {
	
	private List<SoapClientUser> users;
	
	/**
	 * Get user list
	 * @return 
	 */
	@XmlElement( name = "User")
	public List<SoapClientUser> getUsers() {
		
		if(users == null) {
			return users = new ArrayList<SoapClientUser>();
		}
		return users;
	}
	
}
