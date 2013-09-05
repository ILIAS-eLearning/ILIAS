/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

package de.ilias.services.lucene.settings;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.Date;
import java.util.HashMap;

import org.apache.log4j.Logger;

import de.ilias.services.db.DBFactory;
import de.ilias.services.settings.LocalSettings;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class LuceneSettings {
	
	public static final int OPERATOR_AND = 1;
	public static final int OPERATOR_OR = 2;
	
	protected static Logger logger = Logger.getLogger(LuceneSettings.class);
	private static HashMap<String, LuceneSettings> instances = new HashMap<String, LuceneSettings>();
	
	
	private int fragmentSize = 30;
	private int numFragments = 3;
	private int defaultOperator = OPERATOR_AND;
	private Date lastIndexTime = new java.util.Date();
	private int prefixWildcard = 0;
	
	/**
	 * Constructor
	 * @throws SQLException 
	 */
	public LuceneSettings() throws SQLException {
		super();
		readSettings();
	}
	
	
	/**
	 * Get singleton instance for a client
	 * @return FieldInfo 
	 * @throws SQLException 
	 */
	public static LuceneSettings getInstance() throws SQLException {
		
		return getInstance(LocalSettings.getClientKey());
	}

	/**
	 * @param clientKey
	 * @return
	 * @throws SQLException 
	 */
	public static LuceneSettings getInstance(String clientKey) throws SQLException {

		if(instances.containsKey(clientKey)) {
			return instances.get(clientKey);
		}
		
		instances.put(clientKey, new LuceneSettings());
		return instances.get(clientKey);
	}

	public boolean refresh() throws SQLException {
		
		readSettings();
		return true;
	}
	

	/**
	 * @return the fragmentSize
	 */
	public int getFragmentSize() {
		return fragmentSize;
	}

	/**
	 * @param fragmentSize the fragmentSize to set
	 */
	public void setFragmentSize(int fragmentSize) {
		this.fragmentSize = fragmentSize;
	}

	/**
	 * @return the numFragments
	 */
	public int getNumFragments() {
		return numFragments;
	}

	/**
	 * @param numFragments the numFragments to set
	 */
	public void setNumFragments(int numFragments) {
		this.numFragments = numFragments;
	}

	/**
	 * @return the defaultOperator
	 */
	public int getDefaultOperator() {
		return defaultOperator;
	}

	/**
	 * @param defaultOperator the defaultOperator to set
	 */
	public void setDefaultOperator(int defaultOperator) {
		this.defaultOperator = defaultOperator;
	}
	
	public boolean isPrefixWildcardQueryEnabled()
	{
		return this.prefixWildcard > 0 ? true : false;
	}
	
	public void enablePrefixWildcardQuery(int stat) {
		
		this.prefixWildcard = stat;
	}
	
	public static void writeLastIndexTime() throws SQLException {
		
		Statement sta = DBFactory.factory().createStatement();
		sta.executeUpdate("DELETE FROM settings " + 
				"WHERE module = 'common' AND keyword = 'lucene_last_index_time'");
		try {
			sta.close();
		} catch (SQLException e) {
			logger.warn(e);
		}

		
		String query = "INSERT INTO settings (value,module,keyword) " +
			"VALUES (?,?,?) ";
		PreparedStatement pst = DBFactory.getPreparedStatement(query);
		
		pst.setString(1,String.valueOf(new java.util.Date().getTime()/1000));
		pst.setString(2,"common");
		pst.setString(3, "lucene_last_index_time");
		pst.executeUpdate();
		DBFactory.closePreparedStatement(query);
	}

	/**
	 * @param date
	 */
	public void setLastIndexTime(Date date) {

		lastIndexTime = date;
	}
	
	/**
	 * get datetime of last index
	 * @return
	 */
	public Date getLastIndexTime() {
		return lastIndexTime;
	}

	
	/**
	 * @throws SQLException 
	 * 
	 */
	private void readSettings() throws SQLException {

		Statement sta = DBFactory.factory().createStatement();
		ResultSet res = sta.executeQuery("SELECT value FROM settings WHERE module = 'common' " +
			"AND keyword = 'lucene_default_operator'");

		while(res.next()) {
			setDefaultOperator(Integer.parseInt(res.getString("value")));
			logger.info("Default Operator is: " + getDefaultOperator());
		}
		
		// begin-patch mime_filter
		res = sta.executeQuery("SELECT value FROM settings WHERE module = 'common' " +
			"AND keyword = 'lucene_prefix_wildcard'");
		while(res.next()) {
			this.enablePrefixWildcardQuery(Integer.parseInt(res.getString("value")));
			logger.info("Prefix wildcard queries enabled: " + (this.isPrefixWildcardQueryEnabled() ? "yes" : "no"));
		}
		
		
		
		res = sta.executeQuery("SELECT value FROM settings WHERE module = 'common' " +
			"AND keyword = 'lucene_fragment_size'");
		while(res.next()) {
			setFragmentSize(Integer.parseInt(res.getString("value")));
			logger.info("Fragment size is: " + getFragmentSize());
		}

		res = sta.executeQuery("SELECT value FROM settings WHERE module = 'common' " +
			"AND keyword = 'lucene_fragment_count'");
		while(res.next()) {
			setNumFragments(Integer.parseInt(res.getString("value")));
			logger.info("Number of fragments is: " + getNumFragments());
		}

		res = sta.executeQuery("SELECT value FROM settings WHERE module = 'common' " +
			"AND keyword = 'lucene_last_index_time'");
		while(res.next()) {
			logger.info("Date:" + res.getString("value"));
			Date date = new Date((long) Integer.parseInt(res.getString("value")) * 1000);
			logger.info(date);
			setLastIndexTime(
					new Date((long) Integer.parseInt(
							res.getString("value")) * 1000));
		}
		try {
			sta.close();
			res.close();
		} catch (SQLException e) {
			logger.warn(e);
		}
		
	}


}
