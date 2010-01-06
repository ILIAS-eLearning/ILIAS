package de.ilias.services.lucene.index.file.path;

import java.io.File;
import java.sql.ResultSet;
import java.sql.SQLException;

import org.apache.log4j.Logger;

import de.ilias.services.db.DBFactory;
import de.ilias.services.lucene.index.CommandQueueElement;
import de.ilias.services.settings.ClientSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;

/**
 * Creates the filesystem path to exercise assignments. 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */

public class ExerciseAssignmentPathCreator implements PathCreator {

	protected Logger logger = Logger.getLogger(ExerciseAssignmentPathCreator.class);
	
	
	public File buildFile(CommandQueueElement el, ResultSet res)
			throws PathCreatorException {

		int objId = el.getObjId();
		StringBuilder fullPath = new StringBuilder();
		
		File file;
		
		try {
			
			fullPath.append(ClientSettings.getInstance(LocalSettings.getClientKey()).getDataDirectory().getAbsolutePath());
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append(ClientSettings.getInstance(LocalSettings.getClientKey()).getClient());
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append("ilExercise");
			fullPath.append(System.getProperty("file.separator"));
			fullPath.append(PathUtils.buildSplittedPathFromId(objId,"exc"));
			fullPath.append("ass_" + String.valueOf(DBFactory.getInt(res, "id")));
			
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
	 * 
	 */
	public File buildFile(CommandQueueElement el) throws PathCreatorException {

		return buildFile(el, null);
	}

}
