/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

package de.ilias.services.settings;

import org.apache.commons.configuration2.INIConfiguration;
import org.apache.commons.configuration2.SubnodeConfiguration;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import java.io.*;

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class CommonsIniFileParser
{
    Logger logger = LogManager.getLogger(CommonsIniFileParser.class);

    private ServerSettings serverSettings = null;


    public CommonsIniFileParser()
    {
    }


    public void parseSettings(String path, boolean parseClientSettings) throws ConfigurationException
    {
        serverSettings = ServerSettings.getInstance();
        ClientSettings clientSettings;
        INIConfiguration ini = new INIConfiguration();

        logger.debug("Start parsing {}", path);
        try (FileReader fileReader = new FileReader(path)) {
            logger.debug("Ini read...");
            ini.read(fileReader);
            logger.debug("Ini read.");
            for (String section: ini.getSections()) {
                SubnodeConfiguration sectionConfig = ini.getSection(section);
                // server settings from ilServer.ini
                if (section.equals("Server")) {
                    logger.debug("section server");
                    if(sectionConfig.containsKey("IpAddress")) {
                        logger.debug("Ip address {}", getConfig(ini, section, "IpAddress", false));
                        serverSettings.setHost(getConfig(ini, section, "IpAddress", false));
                    }
                    if(sectionConfig.containsKey("Port")) {
                        logger.debug("Port {}", getConfig(ini, section, "Port", false));
                        serverSettings.setPort(getConfig(ini, section, "Port", false));
                    }
                    if(sectionConfig.containsKey("IndexPath")) {
                        logger.debug("IndexPath {}", getConfig(ini, section, "IndexPath", false));
                        serverSettings.setIndexPath(getConfig(ini, section, "IndexPath", false));
                    }
                    if(sectionConfig.containsKey("LogFile")) {
                        logger.debug("LogFile {}", getConfig(ini, section, "LogFile", false));
                        serverSettings.setLogFile(getConfig(ini, section, "LogFile", false));
                    }
                    if(sectionConfig.containsKey("LogLevel")) {
                        logger.debug("LogLevel {}", getConfig(ini, section, "LogLevel", false));
                        serverSettings.setLogLevel(getConfig(ini, section, "LogLevel", false));
                    }
                    if(sectionConfig.containsKey("NumThreads")) {
                        logger.debug("NumThreads {}", getConfig(ini, section, "NumThreads", false));
                        serverSettings.setThreadNumber(getConfig(ini, section, "NumThreads", false));
                    }
                    logger.debug("Check ram size");
                    if(sectionConfig.containsKey("RAMBufferSize")) {
                        logger.debug("RAM {}", getConfig(ini, section, "RAMBufferSize", false));
                        serverSettings.setRAMSize(getConfig(ini, section, "RAMBufferSize", false));
                    }
                    logger.debug("Check file size");
                    if(sectionConfig.containsKey("IndexMaxFileSizeMB")) {
                        logger.debug("Index {}", getConfig(ini, section, "IndexMaxFileSizeMB", false));
                        serverSettings.setMaxFileSizeMB(getConfig(ini, section, "IndexMaxFileSizeMB", false));
                    }
                }
                logger.debug("check section client");
                if (section.startsWith("Client") && parseClientSettings) {
                    logger.debug("section client");
                    if(sectionConfig.containsKey("ClientId")) {
                        String client = getConfig(ini, section, "ClientId", false);
                        String nic;
                        logger.debug("Client {}", client);
                        if(sectionConfig.containsKey("NicId"))
                            nic = getConfig(ini, section, "NicId", false);
                        else
                            nic = "0";
                        clientSettings = ClientSettings.getInstance(client, nic);
                        if(sectionConfig.containsKey("IliasIniPath")) {
                            clientSettings.setIliasIniFile(getConfig(ini, section, "IliasIniPath", false));
                            // Now parse the ilias.ini file
                            parseClientData(clientSettings);
                        }
                    } else {
                        logger.error("No ClientId given for section: {} ", section);
                        throw new ConfigurationException("No ClientId given for section: " + section);
                    }
                }
            }
        } catch (IOException e) {
            logger.fatal("Parsing ini file failed: {}", path);
            throw new ConfigurationException("Parsing ini file failed.", e);
        } catch (org.apache.commons.configuration2.ex.ConfigurationException e) {
            logger.fatal("Parsing ini file failed: {} ", path);
            throw new ConfigurationException("Parsing ini file failed.", e);
        }
    }

    private String getConfig(INIConfiguration ini, String section, String key, boolean replaceQuotes)
    {
        return purgeString(ini.getSection(section).getProperty(key).toString(), replaceQuotes);
    }

    private String purgeString(String dirty, boolean replaceQuotes)
    {
        if(replaceQuotes) {
            return dirty.replace('"',' ').trim();
        }
        else {
            return dirty.trim();
        }
    }

    private void parseClientData(ClientSettings clientSettings) throws ConfigurationException
    {
        INIConfiguration ini = new INIConfiguration();
        try (StringReader stringReader = convertIniFile(clientSettings.getIliasIniFile())) {
            ini.read(stringReader);

            clientSettings.setDataDirectory(getConfig(ini, "clients", "datadir", true));
            clientSettings.setAbsolutePath(getConfig(ini, "server", "absolute_path", true));

            String dataName = getConfig(ini, "clients", "path", true);
            String iniFileName = getConfig(ini, "clients", "inifile", true);

            clientSettings.setClientIniFile(clientSettings.getAbsolutePath().getCanonicalPath() +
                    System.getProperty("file.separator") +
                    dataName + System.getProperty("file.separator") +
                    clientSettings.getClient() + System.getProperty("file.separator") +
                    iniFileName);
            clientSettings.setIndexPath(ServerSettings.getInstance().getIndexPath() +
                    System.getProperty("file.separator") +
                    clientSettings.getClientKey());
        } catch (IOException e) {
            logger.fatal("Parsing ilias ini file failed: {}", clientSettings.getIliasIniFile().getAbsolutePath());
            throw new ConfigurationException("Parsing ilias ini file failed.", e);
        } catch (org.apache.commons.configuration2.ex.ConfigurationException e) {
            logger.fatal("Parsing ilias ini file failed: {}", clientSettings.getIliasIniFile().getAbsolutePath());
            throw new ConfigurationException("Parsing ini file failed.", e);
        }

        try (StringReader stringReader = convertIniFile(clientSettings.getClientIniFile())) {
            ini.read(stringReader);

            clientSettings.setDbType(getConfig(ini, "db", "type", true));
            clientSettings.setDbHost(getConfig(ini, "db", "host", true));
            clientSettings.setDbPort(getConfig(ini, "db", "port", true));
            clientSettings.setDbUser(getConfig(ini, "db", "user", true));
            clientSettings.setDbPass(getConfig(ini, "db", "pass", true));
            clientSettings.setDbName(getConfig(ini, "db", "name", true));

            logger.debug("Client ID: {}", clientSettings.getClient());
            logger.debug("DB Type: {}", clientSettings.getDbType());
            logger.debug("DB Host: {}", clientSettings.getDbHost());
            logger.debug("DB Port: {}", clientSettings.getDbPort());
            logger.debug("DB Name: {}", clientSettings.getDbName());
            logger.debug("DB User: {}", clientSettings.getDbUser());
            logger.debug("DB Pass: {}", clientSettings.getDbPass());

        } catch (IOException e) {
            logger.fatal("Parsing client ini file failed: {}", clientSettings.getClientIniFile().getAbsolutePath());
            throw new ConfigurationException("Parsing client ini file failed.", e);
        } catch (org.apache.commons.configuration2.ex.ConfigurationException e) {
            logger.fatal("Parsing client ini file failed: {}", clientSettings.getClientIniFile().getAbsolutePath());
            throw new ConfigurationException("Parsing client ini file failed.", e);
        }
    }

    private StringReader convertIniFile(File iniFile) throws ConfigurationException
    {
        try {
            String output;
            InputStreamReader reader = new InputStreamReader(new FileInputStream(iniFile));

            int c;
            StringBuilder builder = new StringBuilder();

            while((c = reader.read())!=-1){
                builder.append((char)c);
            }
            output = builder.toString();
            output = output.replaceFirst("<\\?php /\\*","");
            output = output.replaceFirst("\\*/ \\?>","");
            return new StringReader(output);
        }
        catch (FileNotFoundException e) {
            logger.fatal("Cannot find ini file: {}", e.getMessage());
            throw new ConfigurationException(e);
        }
        catch (IOException e) {
            logger.error("Caught IOException when trying to convert ini file: " + e.getMessage());
            throw new ConfigurationException(e);
        }
    }
}
