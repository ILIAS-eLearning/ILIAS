<?php
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

/**
 * @classDescription Creates a java server ini file for the current client
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilRpcIniFileWriter
{
    protected $ini = '';
    
    protected $host;
    protected $port;
    protected $indexPath;
    protected $logPath;
    protected $logLevel;
    protected $numThreads;
    protected $max_file_size;
    
    
    public function __construct()
    {
    }
    
    public function write()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $ilIliasIniFile = $DIC['ilIliasIniFile'];
        
        // Main section
        $this->ini = "[Server]\n";
        $this->ini .= "IpAddress = " . $this->getHost() . "\n";
        $this->ini .= "Port = " . $this->getPort() . "\n";
        $this->ini .= "IndexPath = " . $this->getIndexPath() . "\n";
        $this->ini .= "LogFile = " . $this->getLogPath() . "\n";
        $this->ini .= "LogLevel = " . $this->getLogLevel() . "\n";
        $this->ini .= "NumThreads = " . $this->getNumThreads() . "\n";
        $this->ini .= "RamBufferSize = 256\n";
        $this->ini .= "IndexMaxFileSizeMB = " . $this->getMaxFileSize() . "\n";
        
        $this->ini .= "\n";
        
        $this->ini .= "[Client1]\n";
        $this->ini .= "ClientId = " . CLIENT_ID . "\n";
        $this->ini .= "NicId = " . $ilSetting->get('inst_id', 0) . "\n";
        $this->ini .= "IliasIniPath = " . $ilIliasIniFile->readVariable('server', 'absolute_path') . DIRECTORY_SEPARATOR . "ilias.ini.php\n";
        
        return true;
    }
    
    public function getIniString()
    {
        return $this->ini;
    }

    /**
     * Returns $host.
     *
     * @see ilRpcIniFileWriter::$host
     */
    public function getHost()
    {
        return $this->host;
    }
    /**
     * Sets $host.
     *
     * @param object $host
     * @see ilRpcIniFileWriter::$host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }
    /**
     * Returns $indexPath.
     *
     * @see ilRpcIniFileWriter::$indexPath
     */
    public function getIndexPath()
    {
        return $this->indexPath;
    }
    /**
     * Sets $indexPath.
     *
     * @param object $indexPath
     * @see ilRpcIniFileWriter::$indexPath
     */
    public function setIndexPath($indexPath)
    {
        $this->indexPath = $indexPath;
    }
    /**
     * Returns $logLevel.
     *
     * @see ilRpcIniFileWriter::$logLevel
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }
    /**
     * Sets $logLevel.
     *
     * @param object $logLevel
     * @see ilRpcIniFileWriter::$logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }
    /**
     * Returns $logPath.
     *
     * @see ilRpcIniFileWriter::$logPath
     */
    public function getLogPath()
    {
        return $this->logPath;
    }
    /**
     * Sets $logPath.
     *
     * @param object $logPath
     * @see ilRpcIniFileWriter::$logPath
     */
    public function setLogPath($logPath)
    {
        $this->logPath = $logPath;
    }
    /**
     * Returns $numThreads.
     *
     * @see ilRpcIniFileWriter::$numThreads
     */
    public function getNumThreads()
    {
        return $this->numThreads;
    }
    /**
     * Sets $numThreads.
     *
     * @param object $numThreads
     * @see ilRpcIniFileWriter::$numThreads
     */
    public function setNumThreads($numThreads)
    {
        $this->numThreads = $numThreads;
    }
    /**
     * Returns $port.
     *
     * @see ilRpcIniFileWriter::$port
     */
    public function getPort()
    {
        return $this->port;
    }
    /**
     * Sets $port.
     *
     * @param object $port
     * @see ilRpcIniFileWriter::$port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Set max file size mb
     * @param int $a_fs
     */
    public function setMaxFileSize($a_fs)
    {
        $this->max_file_size = $a_fs;
    }

    /**
     * Get max file size mb
     * @return int
     */
    public function getMaxFileSize()
    {
        return $this->max_file_size;
    }
}
