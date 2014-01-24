/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.soap.api;

import java.io.File;
import javax.xml.bind.annotation.*;

/**
 * Class SoapClientFileContent
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlAccessorType(XmlAccessType.FIELD)
public class SoapClientFileContent {
	
	@XmlValue
	private String content;
	
	@XmlAttribute
	private String mode = "REST";
	
	@XmlTransient
	private File contentFile = null;
	
	public String getValue() {
		return this.content;
	}
	
	public void setValue(String value) {
		this.content = value;
	}
	
	public void setContentFile(File content) {
		contentFile = content;
	}
	
	public File getContentFile() {
		return this.contentFile;
	}

	
}
