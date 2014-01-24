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
 * Class RestGetFileResponse
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
@XmlRootElement
@XmlAccessorType(XmlAccessType.FIELD)
public class RestGetFileResponse {
	
	@XmlElement( name = "content")
	public File content;
	
	public File getContent() {
		return this.content;
	}
	
	public void setContent(File file) {
		this.content = file;
	}
}
