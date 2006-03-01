/*
	+-----------------------------------------------------------------------------+
	| LT4eL - Language Technology for e-Learning                                  |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2006 LT4eL Consortium                                         |
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


package lt4el;

/**
 * @author Stefan Meyer <smeyer@databay.de>
 * @author Alex Killing <alex.killing@gmx.de>
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

public class ltServerSettings {

    	//private Logger logger = Logger.getLogger(this.getClass().getName());

        private static ltServerSettings instance = null;
        
        private InetAddress host = null;
        private int port;
        private File index_path = null;
        
        
         private ltServerSettings(String property_path)
         throws ltConfigurationException
        {
             loadProperties(property_path);
        }
        
        public static ltServerSettings getInstance(String[] args)
        throws ltConfigurationException
        {
            if(instance == null)
            {
                if(args.length != 1) {
                    throw new ltConfigurationException("Usage: java -jar ltServer <Path to properties>");
                }
                instance = new ltServerSettings(args[0]);
            }
            return instance;
        }
        public static ltServerSettings getInstance()
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
        throws ltConfigurationException 
        {
            try {
                this.host = InetAddress.getByName(host);
            } catch (UnknownHostException e) {
                throw new ltConfigurationException("Unknown host" + host);
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
        throws  ltConfigurationException 
        {
            this.index_path = new File(index_path);
            
            if(!this.index_path.isAbsolute())
            {
                throw new ltConfigurationException("Absolute path required: " + index_path );
            }
            if(!this.index_path.canWrite())
            {
                throw new ltConfigurationException("Path not writable: " + index_path );
            }
            if(!this.index_path.isDirectory())
            {
                throw new ltConfigurationException("Directory name required: " + index_path );
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
            throws  ltConfigurationException { 
            
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
                throw new ltConfigurationException("No valid property file given: " + e);
            } catch (IOException e) {
                throw new ltConfigurationException("Cannot read property file: " + e);
            } catch (Exception e) {
                throw new ltConfigurationException("Error read properties: " + e);
            }
            
            return;
        }
        
        private void initLogger(String logfile)
        throws ltConfigurationException
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
                throw new ltConfigurationException("Error appending LogFile: " + e);
            }
        }
}
