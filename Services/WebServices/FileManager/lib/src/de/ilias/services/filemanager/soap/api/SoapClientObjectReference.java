/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;
import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlAttribute;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlElementWrapper;

/**
 * Class SoapClientObjectReference
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType(XmlAccessType.FIELD)
public class SoapClientObjectReference {
	
	@XmlAttribute( name = "ref_id")
	private int refId;
	
	@XmlAttribute( name = "parent_id")
	private int parentId;
	
	@XmlAttribute( name = "accessInfo")
	private String accessInfo;
	
	@XmlElement( name = "Operation")
	private List<String> operations;
	
	@XmlElementWrapper( name = "Path")
	@XmlElement( name = "Element")
	private List<SoapClientObjectReferencePath> pathElements;
	
	/**
	 * get ref_id
	 * @return 
	 */
	public int getRefId() {
		return this.refId;
	}
	
	/**
	 * get parent id
	 * @return 
	 */
	public int getParentId() {
		return this.parentId;
	}
	
	/**
	 * get access info
	 * @return 
	 */
	public String getAccessInfo() {
		return this.accessInfo;
	}
	
	/**
	 * get operations
	 * @return 
	 */
	public List<String> getOperations() {
		
		if(operations == null) {
			return operations = new ArrayList<String>();
		}
		return operations;
	}
	
	/**
	 * Get path elements
	 * @return 
	 */
	public List<SoapClientObjectReferencePath> getPathElements() {
		
		if(pathElements == null) {
			return this.pathElements = new ArrayList<SoapClientObjectReferencePath>();
		}
		return pathElements;
	}
}
