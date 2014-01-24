/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.util.logging.Logger;
import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlAttribute;
import javax.xml.bind.annotation.XmlTransient;

/**
 * Class SoapClientFileLock
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType(XmlAccessType.FIELD)
public class SoapClientFileLock {
	
	@XmlTransient
	protected static final Logger logger = Logger.getLogger(SoapClientFileLock.class.getName());
	
	@XmlAttribute( name = "until")
	private String until = "";
	
	@XmlAttribute( name = "user_id")
	private String user_id = "";

	@XmlAttribute( name = "enable_download")
	private String enable_download = "";
	
	/**
	 * Get until date
	 * @return 
	 */
	public String getUntil() {
		return until;
	}
	
	public long getRemainingSeconds() {
		
		long ut = FileManagerUtils.textToInt(until);
		
		if(ut <= 0) {
			return 0;
		}
		
		ut = ut - (System.currentTimeMillis() / 1000L);
		
		if(ut > 0) {
			logger.info(String.valueOf(ut));
			return ut;
		}
		return 0L;
	}
	
	/**
	 * Get user id
	 * @return 
	 */
	public String getUserId() {
		return user_id;
	}
	
	/**
	 * check if download is enabled
	 */
	public boolean isDownloadEnabled() {
		return enable_download.equalsIgnoreCase("1");
	}
	
	/**
	 * Enable download
	 * @param status 
	 */
	public void enableDownload(boolean status) {
		enable_download = status ? "1" : "0";
	}
	
	/**
	 * Set until
	 * @param until 
	 */
	public void setUntil(long until) {
		this.until = String.valueOf(until);
	}
	
	/**
	 * Set user id
	 * @param userId 
	 */
	public void setUserId(int userId) {
		this.user_id = String.valueOf(userId);
	}
}
