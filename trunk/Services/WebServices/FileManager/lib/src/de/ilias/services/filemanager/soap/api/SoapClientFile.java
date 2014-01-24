/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import de.ilias.services.filemanager.utils.Base64;
import de.ilias.services.filemanager.utils.FileManagerUtils;
import java.io.*;
import java.util.logging.Logger;
import javax.xml.bind.annotation.*;

/**
 * Class SoapClientUser
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType( XmlAccessType.FIELD)
@XmlRootElement(name = "File")
public class SoapClientFile {
	
	@XmlTransient
	private static final Logger logger = Logger.getLogger(SoapClientFile.class.getName());
	
	private String Filename;
	private String Title;
	private String Description;
	private SoapClientFileLock Lock;
	
	@XmlElement( name = "Content")
	private SoapClientFileContent Content = new SoapClientFileContent();
	
	@XmlTransient
	private File tmp;
	
	
	/**
	 * Get filename
	 * @return 
	 */
	public String getFilename() {
		return this.Filename;
	}
	
	/**
	 * Set filename
	 * @param name 
	 */
	public void setFilename(String name) {
		this.Filename = name;
	}
	
	/**
	 * Get file
	 * @return 
	 */
	public String getTitle() {
		return this.Title;
	}
	
	/**
	 * Set title
	 * @param title 
	 */
	public void setTitle(String title) {
		this.Title = title;
	}
	
	/**
	 * Get description
	 * @return 
	 */
	public String getDescription() {
		return this.Description;
	}
	
	/**
	 * get file
	 * @return 
	 */
	public File getFile() {
		return tmp;
	}
	
	/**
	 * Get file lock info
	 * @return 
	 */
	public SoapClientFileLock getFileLock() {
		if(this.Lock == null) {
			this.Lock = new SoapClientFileLock();
			return this.Lock;
		}
		return this.Lock;
	}
	
	/**
	 * Set file lock
	 * @param lock 
	 */
	public void setFileLock(SoapClientFileLock lock) {
		this.Lock = lock;
	}
	
	/**
	 * get content
	 */
	public SoapClientFileContent getContent() {
		return this.Content;
	}
	
	public void setContent(SoapClientFileContent cont) {
		this.Content = cont;
	}
	
	/**
	 * @todo check performance
	 * Write to temp file
	 * @throws IOException 
	 */
	public File writeToTempFile() throws IOException {
				
		File tmpDir,tmp;
		
		tmpDir = FileManagerUtils.createTempDirectory("ilfm_");
		tmpDir.deleteOnExit();
		
		tmp = new File(tmpDir,getFilename());
		tmp.deleteOnExit();
		
		
		Base64.decodeFileToFile(getContent().getContentFile().getAbsolutePath(), tmp.getAbsolutePath());
		logger.info("Content written to: " + tmp.getAbsolutePath());
		return tmp;
	}
	
	/**
	 * Write to file
	 * @param target
	 * @return
	 * @throws FileNotFoundException
	 * @throws IOException 
	 */
	public File writeToFile(File target) throws FileNotFoundException, IOException {
		
		FileOutputStream stream;

		logger.info(target.getAbsolutePath());
		Base64.decodeFileToFile(getContent().getContentFile().getAbsolutePath(), target.getAbsolutePath());
		return target;
	}
}
