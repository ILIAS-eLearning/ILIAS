/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.rest.api;

import java.io.File;
import javax.xml.bind.annotation.XmlAccessType;
import javax.xml.bind.annotation.XmlAccessorType;
import javax.xml.bind.annotation.XmlElement;
import javax.xml.bind.annotation.XmlRootElement;

/**
 * Class RestPostFileRequest
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlRootElement
@XmlAccessorType(XmlAccessType.FIELD)
public class RestPostFileRequest {
	
	@XmlElement( name = "content")
	public String content = "MTIzCgo=";
	public File contentFile;
	
	
	public File getContent() {
		return this.contentFile;
	}
	
	public void setContent(File file) {
		this.contentFile = file;
	}
}