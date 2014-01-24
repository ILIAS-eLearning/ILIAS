/*
 * 
 */
package de.ilias.services.lucene.index.file.path;

import de.ilias.services.db.DBFactory;
import de.ilias.services.lucene.index.CommandQueueElement;
import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;
import java.io.File;
import java.sql.ResultSet;
import java.sql.SQLException;
import org.apache.log4j.Logger;

/**
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class MailAttachmentPathCreator implements PathCreator {

	public static Logger logger = Logger.getLogger(MailAttachmentPathCreator.class);

	
	public File buildFile(CommandQueueElement el, ResultSet res) throws PathCreatorException {
		
		StringBuilder fullPath = new StringBuilder();
		
		File file;
		
		try {
			fullPath.append(ClientSettings.getInstance(LocalSettings.getClientKey()).getDataDirectory().getAbsolutePath());
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append(ClientSettings.getInstance(LocalSettings.getClientKey()).getClient());
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append("mail");
			fullPath.append(System.getProperty("file.separator"));
			
			fullPath.append(String.valueOf(DBFactory.getString(res, "path")));

			logger.info("Try to read from path: " + fullPath.toString());
			
			file = new File(fullPath.toString());
			if(file.exists() && file.canRead()) {
				return file;
			}
			throw new PathCreatorException("Cannot access directory: " + fullPath.toString());
		}
		catch (ConfigurationException e) {
			throw new PathCreatorException(e);
		}
		catch (SQLException e) {
			throw new PathCreatorException(e);
		}
		catch (NullPointerException e) {
			throw new PathCreatorException(e);
		} 

	}

	/**
	 * not used 
	 * @todo check and throw exception
	 * @param el
	 * @return
	 * @throws PathCreatorException 
	 */
	public File buildFile(CommandQueueElement el) throws PathCreatorException {
		
		return this.buildFile(el, null);
		
	}
	
}
