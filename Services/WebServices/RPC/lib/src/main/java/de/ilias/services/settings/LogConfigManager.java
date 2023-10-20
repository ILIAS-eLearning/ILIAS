/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package de.ilias.services.settings;

import org.apache.commons.configuration2.INIConfiguration;
import org.apache.commons.configuration2.SubnodeConfiguration;
import org.apache.logging.log4j.Level;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.apache.logging.log4j.core.Filter;
import org.apache.logging.log4j.core.LoggerContext;
import org.apache.logging.log4j.core.appender.RollingFileAppender;
import org.apache.logging.log4j.core.appender.rolling.DefaultRolloverStrategy;
import org.apache.logging.log4j.core.appender.rolling.SizeBasedTriggeringPolicy;
import org.apache.logging.log4j.core.config.Configuration;
import org.apache.logging.log4j.core.config.LoggerConfig;
import org.apache.logging.log4j.core.filter.ThresholdFilter;
import org.apache.logging.log4j.core.layout.PatternLayout;

import java.io.File;
import java.io.FileReader;
import java.io.IOException;

public class LogConfigManager {

	private final Logger logger = LogManager.getLogger(LogConfigManager.class);
	
	private File file;
	private Level level;

	private boolean isInitialized = false;
	
	
	public Level getLogLevel()
	{
		return this.level;
	}
	
	public File getLogFile()
	{
		return this.file;
	}

	public void setLogLevel(String logLevel)
	{
		this.level = Level.toLevel(logLevel.trim(),Level.INFO);
	}

	public void setLogFile(String logFile) throws ConfigurationException, IOException {

		this.file = new File(logFile);
		if(!this.file.isAbsolute()) {
			logger.error("Absolute path to logfile required: {}", logFile);
			throw new ConfigurationException("Absolute path to logfile required: " + logFile);
		}
		if(this.file.isDirectory()) {
			logger.error("Absolute path to logfile required. Directory name given: {}", logFile);
			throw new ConfigurationException("Absolute path to logfile required: " + logFile);
		}
		if(this.file.createNewFile()) {
			logger.debug("Creating new log file {}", logFile);
		}
		else {
			logger.debug("Using existing log file: {}", this.file.getAbsolutePath());
		}
		if(!this.file.canWrite()) {
			throw new ConfigurationException("Cannot write to log file: " + logFile);
		}
	}




	public void parse(String path) throws ConfigurationException {

		INIConfiguration ini = new INIConfiguration();
		try (FileReader fileReader = new FileReader(path)) {
			ini.read(fileReader);
			for (String section : ini.getSections()) {
				if (section.equals("Server")) {
					SubnodeConfiguration sectionConfig = ini.getSection(section);
					if (sectionConfig.containsKey("LogLevel")) {
						setLogLevel(purgeString(sectionConfig.getProperty("LogLevel").toString()));
					}
					if (sectionConfig.containsKey("LogFile")) {
						setLogFile(purgeString(sectionConfig.getProperty("LogFile").toString()));
					}
				}
			}
		} catch (org.apache.commons.configuration2.ex.ConfigurationException e) {
			throw new ConfigurationException(e);
		} catch (IOException e) {
			throw new ConfigurationException(e);
		}
	}

	public void initLogManager()
	{
		if (isInitialized) {
			logger.warn("Logging service already initialized");
		}

		LoggerContext context = (LoggerContext) LogManager.getContext(false);
		Configuration config = context.getConfiguration();
		LoggerConfig rootConfig = config.getLoggerConfig(LogManager.ROOT_LOGGER_NAME);
		LoggerConfig iliasConfig = config.getLoggerConfig("de.ilias");
		LoggerConfig iliasServerConfig = config.getLoggerConfig("de.ilias.ilServer");

		// new rolling file appender
		PatternLayout fileLayout = PatternLayout.newBuilder()
				.withConfiguration(config)
				.withPattern("%d{ISO8601} %-5p %t (%F:%L) - %m%n")
				.build();

		DefaultRolloverStrategy strategy = DefaultRolloverStrategy.newBuilder()
				.withMax("3")
				.withMin("1")
				.withFileIndex("max")
				.withConfig(config)
				.build();

		RollingFileAppender file = RollingFileAppender.newBuilder()
				.setName("RollingFile")
				.withFileName(getLogFile().getAbsolutePath())
				.withFilePattern(getLogFile().getName() + "%d")
				.withStrategy(strategy)
				.withPolicy(SizeBasedTriggeringPolicy.createPolicy("100MB"))
				.setConfiguration(config)
				.setLayout(fileLayout)
				.build();
		file.start();
		config.addAppender(file);


		rootConfig.addAppender(
				file,
				this.getLogLevel(),
				ThresholdFilter.createFilter(Level.DEBUG, Filter.Result.ACCEPT, Filter.Result.NEUTRAL)
		);
		iliasConfig.addAppender(
				file,
				this.getLogLevel(),
				ThresholdFilter.createFilter(Level.DEBUG, Filter.Result.ACCEPT, Filter.Result.NEUTRAL)
		);
		iliasServerConfig.addAppender(
				file,
				this.getLogLevel(),
				ThresholdFilter.createFilter(Level.DEBUG, Filter.Result.ACCEPT, Filter.Result.NEUTRAL)
		);
		context.updateLoggers();
		this.isInitialized = true;
	}

	
	public String purgeString(String dirty,boolean replaceQuotes) {
		
		if(replaceQuotes) {
			return dirty.replace('"',' ').trim();
		}
		else {
			return dirty.trim();
		}
	}
	
	public String purgeString(String dirty) {
		
		return purgeString(dirty,false);
	}
	
}
