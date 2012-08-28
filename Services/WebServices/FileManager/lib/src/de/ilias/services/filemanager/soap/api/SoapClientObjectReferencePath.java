/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import javax.xml.bind.annotation.*;

/**
 * Class SoapClientObjectReferencePath
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType(XmlAccessType.FIELD)
public class SoapClientObjectReferencePath {
	
	@XmlAttribute( name = "ref_id")
	private int refId;
	
	@XmlAttribute( name = "type")
	private String type;
	
	@XmlValue
	private String title;
	
	/**
	 * get ref id
	 * @return 
	 */
	public int getRefId() {
		return this.refId;
	}
	
	/**
	 * Set ref id
	 * @param refId 
	 */
	public void setRefId(int refId) {
		this.refId = refId;
	}
	
	/**
	 * Set type
	 * @param type 
	 */
	public void setType(String type) {
		this.type = type;
	}
	
	/**
	 * get type
	 * @return 
	 */
	public String getType() {
		return this.type;
	}
	
	/**
	 * Set title;
	 * @param title 
	 */
	public void setTitle(String title) {
		this.title = title;
	}
	
	/**
	 * get title
	 * @return 
	 */
	public String getTitle() {
		return this.title;
	}
}
