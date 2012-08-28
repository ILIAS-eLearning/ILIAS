/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.utils;

import de.ilias.services.filemanager.action.ActionHandler;
import de.ilias.services.filemanager.events.ListItemContextMenuEventHandler;
import de.ilias.services.filemanager.skin.SkinFactory;
import java.io.*;
import java.util.UUID;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import javafx.scene.control.Label;
import javafx.scene.image.Image;
import javafx.scene.text.Text;
import javafx.scene.text.TextBuilder;

/**
 * Class FileManagerUtils
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class FileManagerUtils {
	
	protected static final Logger logger = Logger.getLogger(FileManagerUtils.class.getName());
	public static final int ROOT_FOLDER_ID = 1;
	
	
	/**
	 * get fx image by type
	 * @param type
	 * @return 
	 */
	public static Image getImageByType(String type) {
		return new Image(SkinFactory.class.getResourceAsStream("ilias/images/icon_" + type + ".gif"));
	}
	
	/**
	 * get fx image by type
	 * @param type
	 * @return 
	 */
	public static Image getTinyImageByType(String type) {
		return new Image(SkinFactory.class.getResourceAsStream("ilias/images/icon_" + type + "_s.gif"));
	}
	
	/**
	 * Get image 
	 * @param name
	 * @return 
	 */
	public static Image getImageByName(String name) {
		return new Image(SkinFactory.class.getResourceAsStream("ilias/images/" + name));
	}
	
	
	/**
	 * Copy directory recursive
	 * @param source
	 * @param target
	 * @throws FileNotFoundException
	 * @throws IOException 
	 */
	public static void copyDirectory(File source, File target) throws FileNotFoundException, IOException {
		
		if(source.getAbsolutePath().length() >= 1000) {
			return;
		}
		
		if(source.isDirectory()) {
			// create if not exists
			if(!target.exists()) {
				target.mkdir();
			}
			String[] children = source.list();
			for(int i = 0; i < children.length; i++) {
				copyDirectory(
						new File(source, children[i]),
						new File(target, children[i])
				);
			}
		}
		else {
			InputStream in = new FileInputStream(source);
			OutputStream out = new FileOutputStream(target);
			
			byte[] buf = new byte[1024];
			int len;
			while((len = in.read(buf)) > 0) {
				out.write(buf, 0, len);
			}
			in.close();
			out.close();
		}
	}

	/**
	 * Create temp directory
	 * @return 
	 */
	public static File createTempDirectory(String prefix) {
		
		final File tmp = new File(System.getProperty("java.io.tmpdir"));
		File newTmpDir;
		
		int maxAttempts = 10;
		int attempts = 0;
		
		do {
			attempts++;
			if(attempts >= maxAttempts)
				return tmp;
			newTmpDir = new File(tmp,prefix + UUID.randomUUID().toString().substring(0,6));
		} while(newTmpDir.exists());
		
		if(newTmpDir.mkdir()) {
			logger.info(newTmpDir.getAbsolutePath());
			return newTmpDir;
		}
		logger.info(tmp.getAbsolutePath());
		return tmp;
	}
	
	
	/**
	 * Create a temp file
	 * @return 
	 */
	public static File createTempFile() throws IOException {
		
		File tmp;
		tmp = File.createTempFile("ilFm",null);
		tmp.deleteOnExit();
		return tmp;
	}
	
	/**
	 * 
	 * @param type
	 * @return 
	 */
	public static String createTypeToILIASType(int type) {

		switch(type) {
			case ListItemContextMenuEventHandler.ACTION_CREATE_CAT:
				return "cat";
			case ListItemContextMenuEventHandler.ACTION_CREATE_CRS:
				return "crs";
			case ListItemContextMenuEventHandler.ACTION_CREATE_GRP:
				return "grp";
			case ListItemContextMenuEventHandler.ACTION_CREATE_FOLD:
				return "fold";
		}
		return "";
	}
	
	public static Text highlightText(String text, String defaultId) {
		
		TextBuilder tbuilder = TextBuilder.create();
		
		Pattern pat = Pattern.compile("(.*)<span class=\"ilSearchHighlight\">(.*)</span>(.*)");
		Matcher mat = pat.matcher(text);
		while(mat.find()) {
			tbuilder.text(mat.group(1)).id(defaultId);
			tbuilder.text(mat.group(2)).id(defaultId + "Highlight");
		}
		return tbuilder.build();
	}
	
	/**
	 * Convert text to int
	 * @param text
	 * @return 
	 */
	public static int textToInt(String text) {
		
		try {
			if(text.length() > 0) {
				return Integer.valueOf(text);
			}
		}
		catch(NumberFormatException e) {
			;
		}
		return 0;
	}
	
	/**
	 * Increase file (directory) version name
	 * @param name
	 * @param version
	 * @return 
	 */
	public static String increaseVersionName(String name, int version) {
		
		int dot = name.lastIndexOf(".");
		
		// No file extension
		if(dot < 0) {
			return name + " (" + String.valueOf(version) + ")";
		}
		return name.substring(0, dot) +  " (" + String.valueOf(version) + ")" + name.substring(dot);
	}
	
	/**
	 * File to string
	 * @param input
	 * @return 
	 */
	public static String fileToString(File input) {
		
		String res = null;
		DataInputStream in = null;

		try {
			byte[] buffer = new byte[(int) input.length()];
			in = new DataInputStream(new FileInputStream(input));
			in.readFully(buffer);
			return new String(buffer);
		}
		catch(FileNotFoundException e) {
			logger.warning("Cannot find file " + input.getAbsolutePath());
		}
		catch(IOException e) {
			logger.warning("Cannot write from file " + input.getAbsolutePath());
		}
		return new String();
	}
	
	/**
	 * Check allowed file size
	 * @param file
	 * @param fs
	 * @return boolean
	 */
	public static boolean checkAllowedFileSize(File file, int fs) {
		
		return file.length() < (1024 * 1024 * fs);
	}
}
