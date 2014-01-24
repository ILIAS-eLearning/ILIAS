/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.Iterator;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;
import javafx.scene.layout.HBox;
import javax.xml.bind.annotation.*;

/**
 * Class SoapClientObject
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType(XmlAccessType.FIELD)
public class SoapClientObject {
		
	@XmlTransient
	public static final String PROP_FILESIZE = "fileSize";
	
	@XmlTransient
	public static final String PROP_FILE_EXTENSION = "fileExtension";
	
	@XmlTransient
	public static final String PROP_FILE_VERSION = "fileVersion";
	
	@XmlTransient
	public static final String PROP_INFO = "info";
	
	@XmlTransient
	public static final String PROP_FILE_ACCESS = "fileAccess";

	@XmlTransient
	private static Logger logger = Logger.getLogger(SoapClientObject.class.getName());
	
	@XmlAttribute( name = "obj_id")
	private int obj_id;

	@XmlAttribute( name = "type")
	private String type;
	
	@XmlElement (name = "Title")
	private String title;
	
	@XmlElement (name = "Description")
	private String description;
	
	@XmlElement( name = "LastUpdate")
	private String lastUpdate;
	
	@XmlElementWrapper( name = "Properties")
	@XmlElement( name = "Property")
	private List<SoapClientObjectProperty> properties = null;
	
	@XmlElement( name = "References")
	private List<SoapClientObjectReference> references = null;
		
	
	/**
	 * get objId
	 * @return 
	 */
	public int getObjId() {
		return this.obj_id;
	}
	
	/**
	 * Set obj id
	 * @param id 
	 */
	public void setObjId(int id) {
		this.obj_id = id;
	}
	
	/**
	 * get type
	 * @return 
	 */
	public String getType() {
		return this.type;
	}
	
	/**
	 * Set type
	 * @param type 
	 */
	public void setType(String type) {
		this.type = type;
	}
	
	/**
	 * get title
	 * @return 
	 */
	public String getTitle() {
		return this.title;
	}
	
	/**
	 * set title
	 * @param title 
	 */
	public void setTitle(String title) {
		this.title = title;
	}
	
	/**
	 * get description
	 * @return 
	 */
	public String getDescription() {
		return this.description;
	}
	
	/**
	 * Set description
	 * @param description 
	 */
	public void setDescription(String description) {
		this.description = description;
	}
	
	/**
	 * get last update
	 * @return 
	 */
	public String getLastUpdateString() {
		return this.lastUpdate;
	}
	
	/**
	 * Check if object is container
	 * @return 
	 */
	public boolean isContainer() {
		if(getType().compareTo("file") == 0) {
			return false;
		}
		return true;
	}
	
	/**
	 * Check if readable
	 * @return 
	 */
	public boolean isReadable() {
		
		return true;
	}
	
	public boolean isWritable() {
		return true;
	}
	
	/**
	 * Get last update date
	 * @return 
	 */
	public Date getLastUpdate() {
		
		try {
			SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
			Date date = (Date) format.parse(getLastUpdateString());
			return date;
		} 
		catch (ParseException ex) {
			logger.warning("Cannot parse date " + getLastUpdateString());
		}
		return new Date();
	}
	
	/**
	 * Get properties
	 * @return 
	 */
	public List<SoapClientObjectProperty> getProperties() {
		
		if(properties == null) {
			return properties = new ArrayList<SoapClientObjectProperty>();
		}
		return properties;
	}
	
	/**
	 * Get properties by name
	 * @return 
	 */
	public List<SoapClientObjectProperty> getPropertiesByName(String name) {
		
		List<SoapClientObjectProperty> props = new ArrayList<SoapClientObjectProperty>();
		
		for(SoapClientObjectProperty prop : getProperties()) {
			if(name.equalsIgnoreCase(prop.getName())) {
				props.add(prop);
			}
		}
		
		return props;
	}
	
	/**
	 * Get first property by name
	 * @param name
	 * @return 
	 */
	public String getPropertyByName(String name) {
		
		Iterator propIte = getProperties().iterator();
		while(propIte.hasNext()) {
			
			SoapClientObjectProperty prop = (SoapClientObjectProperty) propIte.next();			
			if(prop.getName().equalsIgnoreCase(name)) {
				return prop.getValue();
			}
		}
		return "";
	}
	
	
	/**
	 * Get reference information
	 * @return 
	 */
	public List<SoapClientObjectReference> getReferences() {
		
		if(references == null) {
			return references = new ArrayList<SoapClientObjectReference>();
		}
		return references;
	}
	
	/**
	 * convenience method for reading the ref if from the references
	 * @return 
	 */
	public int getRefId() {
		
		Iterator ite = getReferences().iterator();
		while(ite.hasNext()) {
			
			SoapClientObjectReference ref = (SoapClientObjectReference) ite.next();
			return ref.getRefId();
		}
		return 0;
	}
	
	
	/**
	 * Get parent id
	 * @return 
	 */
	public int getParentId() {

		Iterator ite = getReferences().iterator();
		while(ite.hasNext()) {
			
			SoapClientObjectReference ref = (SoapClientObjectReference) ite.next();
			if(ref.getParentId() > 0)
				return ref.getParentId();
		}
		return 0;
	}
	
	
}
