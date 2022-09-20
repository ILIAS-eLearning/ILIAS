/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package de.ilias.services.settings;

import java.io.FileReader;
import java.io.IOException;
import org.ini4j.Ini;

/**
 *
 * @author stefan
 */
public class LogConfigParser {
	
	String file;
	String level;
	
	
	public String getLogLevel()
	{
		return this.level;
	}
	
	public String getLogFile()
	{
		return this.file;
	}
	
	
	public void parse(String path) throws ConfigurationException {
		
		Ini prefs;
		try {

			prefs = new Ini(new FileReader(path));
			for(Ini.Section section : prefs.values()) {
				
				if(section.getName().equals("Server")) {
					if(section.containsKey("LogFile"))
						this.file = purgeString(section.get("LogFile"));
					if(section.containsKey("LogLevel"))
						this.level  = purgeString(section.get("LogLevel"));
				}
			}
		} catch (IOException e) {
			throw new ConfigurationException(e);
		}
	}
	
	/**
	 * @param dirty
	 * @param replaceQuotes
	 * @return
	 */
	public String purgeString(String dirty,boolean replaceQuotes) {
		
		if(replaceQuotes) {
			return dirty.replace('"',' ').trim();
		}
		else {
			return dirty.trim();
		}
	}
	
	/**
	 * 
	 * @param dirty
	 * @return
	 */
	public String purgeString(String dirty) {
		
		return purgeString(dirty,false);
	}
	
}
