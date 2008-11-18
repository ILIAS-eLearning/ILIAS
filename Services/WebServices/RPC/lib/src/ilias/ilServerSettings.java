/*
+-----------------------------------------------------------------------------------------+
| ILIAS open source                                                                                           |
+-----------------------------------------------------------------------------------------+
| Copyright (c) 1998-2001 ILIAS open source, University of Cologne             |
|                                                                                                                         |
| This program is free software; you can redistribute it and/or                         |
| modify it under the terms of the GNU General Public License                      |
| as published by the Free Software Foundation; either version 2                   |
| of the License, or (at your option) any later version.                                     |
|                                                                                                                         |
| This program is distributed in the hope that it will be useful,                          |
| but WITHOUT ANY WARRANTY; without even the implied warranty of          |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the  |
| GNU General Public License for more details.                                                |
|                                                                                                                          |
| You should have received a copy of the GNU General Public License            |
| along with this program; if not, write to the Free Software                            |
| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
+------------------------------------------------------------------------------------------+
*/

package ilias;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * 
 */
import java.net.InetAddress;
import java.net.UnknownHostException;
import java.util.Properties;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;

import org.apache.log4j.BasicConfigurator;
import org.apache.log4j.Level;
import org.apache.log4j.Logger;
import org.apache.log4j.PatternLayout;
import org.apache.log4j.RollingFileAppender;

public class ilServerSettings {

    	//private Logger logger = Logger.getLogger(this.getClass().getName());

        private static ilServerSettings instance = null;
        
        private InetAddress host = null;
        private int port;
        private File index_path = null;
        
        
        private ilServerSettings(String property_path)
         throws ilConfigurationException
        {
             loadProperties(property_path);
        }
        
        public static ilServerSettings getInstance(String[] args)
        throws ilConfigurationException
        {
            if(instance == null)
            {
                if(args.length != 1) {
                    throw new ilConfigurationException("Usage: java -jar ilServer <Path to properties>");
                }
                instance = new ilServerSettings(args[0]);
            }
            return instance;
        }
        public static ilServerSettings getInstance()
        {
            return instance;
        }
        /**
         * @return Returns the host.
         */
        public InetAddress getHost() {
            return host;
        }

        /**
         * @param host The host to set.
         * @throws LuceneConfigurationException
         */
        public void setHost(String host) 
        throws ilConfigurationException 
        {
            try {
                this.host = InetAddress.getByName(host);
            } catch (UnknownHostException e) {
                throw new ilConfigurationException("Unknown host" + host);
            }
        }
        /**
         * @return Returns the index_path.
         */
        public File getIndexPath() {
            return index_path;
        }
        /**
         * @param index_path The index_path to set.
         * @throws LuceneConfigurationException
         */
        public void setIndexPath(String index_path) 
        throws  ilConfigurationException 
        {
            this.index_path = new File(index_path);
            
            if(!this.index_path.isAbsolute())
            {
                throw new ilConfigurationException("Absolute path required: " + index_path );
            }
            if(!this.index_path.canWrite())
            {
                throw new ilConfigurationException("Path not writable: " + index_path );
            }
            if(!this.index_path.isDirectory())
            {
                throw new ilConfigurationException("Directory name required: " + index_path );
            }
            return;
        }
        /**
         * @return Returns the port.
         */
        public int getPort() {
            return port;
        }
        /**
         * @param port The port to set.
         */
        public void setPort(String port) {
            this.port = Integer.parseInt(port);
        }
        // PRIVATE
        private void loadProperties(String property_path)
            throws  ilConfigurationException { 
            
            FileInputStream fi = null;
            Properties property = new Properties();
            
            try {
                fi = new FileInputStream(property_path);
                property.load(fi);
                setHost(property.getProperty("IpAddress"));
                setPort(property.getProperty("Port"));
                setIndexPath(property.getProperty("IndexPath"));
                initLogger(property.getProperty("LogFile",""));
            } catch( FileNotFoundException e) {
                throw new ilConfigurationException("No valid property file given: " + e);
            } catch (IOException e) {
                throw new ilConfigurationException("Cannot read property file: " + e);
            } catch (Exception e) {
                throw new ilConfigurationException("Error read properties: " + e);
            }
            
            return;
        }
        
        private void initLogger(String logfile)
        throws ilConfigurationException
        {
            Logger logger = Logger.getRootLogger();
            logger.setLevel(Level.INFO);

            if(logfile.length() == 0) {
                BasicConfigurator.configure();
                return;
            }
            try {
                RollingFileAppender file_appender = new RollingFileAppender(
                        new PatternLayout("%5p (%F:%L) - %m%n"),logfile);
                file_appender.setMaxFileSize("10MB");
                logger.addAppender(file_appender);
            
            } catch (IOException e) {
                throw new ilConfigurationException("Error appending LogFile: " + e);
            }
        }
}
