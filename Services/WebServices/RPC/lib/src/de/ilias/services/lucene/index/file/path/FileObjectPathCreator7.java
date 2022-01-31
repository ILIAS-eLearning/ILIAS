/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

package de.ilias.services.lucene.index.file.path;

import java.io.File;
import java.sql.ResultSet;
import java.sql.SQLException;
import de.ilias.services.lucene.index.CommandQueueElement;
import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;


/**
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class FileObjectPathCreator7  implements PathCreator
{
	private static final Logger logger = LogManager.getLogger(FileObjectPathCreator7.class);

	protected String basePath = "storage";
	protected static final String BIN_NAME = "data";
	
	/**
	 * Set bas path
	 * @param bp
	 */
	public void setBasePath(String bp) {
		
		this.basePath = bp;
	}
	

	/**
	 * Get base path
	 * 
	 * @return String basePath
	 */
	public String getBasePath() {
		
		return this.basePath;
	}
	
	
	/**
	 * @see de.ilias.services.lucene.index.file.path.PathCreator#buildPath(de.ilias.services.lucene.index.CommandQueueElement, java.sql.ResultSet)
	 */
	public File buildFile(CommandQueueElement el, ResultSet res)
			throws PathCreatorException {

		StringBuilder fullPath = new StringBuilder();
		StringBuilder versionPath = new StringBuilder();
		
		File file;
		
		try {
			int versionCode = 1;
			int resVersion = res.getInt("version");
			if (resVersion > 0) {
				versionCode = resVersion;
			}

			fullPath.append(ClientSettings.getInstance(LocalSettings.getClientKey()).getDataDirectory().getAbsolutePath());
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append(ClientSettings.getInstance(LocalSettings.getClientKey()).getClient());
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append(getBasePath());
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append(res.getString("resource_path"));
			versionPath.append(fullPath);
			versionPath.append(System.getProperty("file.separator"));
			versionPath.append(String.valueOf(versionCode));
			versionPath.append(System.getProperty("file.separator"));
			versionPath.append(BIN_NAME);
			
			logger.info("Detected file object path is: " + versionPath.toString());

			file = new File(versionPath.toString());
			if (!file.exists()) {
			  throw new PathCreatorException("Cannot find file: " + fullPath.toString());
			}

			if (!file.canRead()) {
			  throw new PathCreatorException("Cannot read file: " + fullPath.toString());
			}

			return file;
		} 
		catch (ConfigurationException | SQLException | NullPointerException e) {
			throw new PathCreatorException(e);
		}
	}

	/**
	 * @see de.ilias.services.lucene.index.file.path.PathCreator#buildPath(de.ilias.services.lucene.index.CommandQueueElement)
	 */
	public File buildFile(CommandQueueElement el) throws PathCreatorException {

		return buildFile(el, null);
	}

	@Override
	public String getExtension(CommandQueueElement el, ResultSet res) {
		
		StringBuilder extension = new StringBuilder();
		try {
			String fileName = res.getString("file_name");
	        int dotIndex = fileName.lastIndexOf(".");
	        if((dotIndex > 0) && (dotIndex < fileName.length())) {
	            extension.append(fileName.substring(dotIndex + 1, fileName.length()));
			}
			logger.info("Extraced extension: " + extension.toString() + " from file name: " + fileName);

		} catch (SQLException ex) {
			logger.error(ex.toString());
		}
		return extension.toString();
	}
}
