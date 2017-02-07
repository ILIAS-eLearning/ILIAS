<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Interface to the ClamAV virus protector
*
* @author        Ralf Schenk <rs@databay.de>
* @version $Id$
*
* @extends ilVirusScanner
*/

require_once "./Services/VirusScanner/classes/class.ilVirusScanner.php";

class ilVirusScannerClamAV extends ilVirusScanner
{
	const ADD_SCAN_PARAMS = '--no-summary -i';
	
        /**
        * Constructor
        * @access        public
        * @param        string virus scanner command
        */
        function ilVirusScannerClamAV($a_scancommand, $a_cleancommand)
        {
                $this->ilVirusScanner($a_scancommand, $a_cleancommand);
                $this->type = "clamav";
                $this->scanZipFiles = true;
        }
	
	/**
	 * @return string $scanCommand
	 */
	protected function buildScanCommand()
	{
		return $this->scanCommand.' '.self::ADD_SCAN_PARAMS;
	}
	
	/**
	 * @param string $buffer (any data, binary)
	 * @return bool $infected
	 */
	public function scanBuffer($buffer)
	{
		return false;
	}

        /**
        * scan a file for viruses
        *
        * @param        string        path of file to check
        * @param        string        original name of the file to ckeck
        * @return   string  virus message (empty if not infected)
        * @access        public
        */
        function scanFile($a_filepath, $a_origname = "")
        {
                // This function should:
                // - call the external scanner for a_filepath
                // - set scanFilePath to a_filepath
                // - set scanFileOrigName to a_origname
                // - set scanFileIsInfected according the scan result
                // - set scanResult to the scanner output message
                // - call logScanResult() if file is infected
                // - return the scanResult, if file is infected
                // - return an empty string, if file is not infected

                $this->scanFilePath = $a_filepath;
                $this->scanFileOrigName = $a_origname;

                // Call of antivir command
                $cmd = $this->buildScanCommand()." ".$a_filepath." 2>&1";
                exec($cmd, $out, $ret);
                $this->scanResult = implode("\n", $out);

                // sophie could be called
                if (ereg("FOUND", $this->scanResult))
                {
                        $this->scanFileIsInfected = true;
                        $this->logScanResult();
                        return $this->scanResult;
                }
                else
                {
                        $this->scanFileIsInfected = false;
                        return "";
                }

                // antivir has failed (todo)
                $this->log->write("ERROR (Virus Scanner failed): "
                                                . $this->scanResult
                                                . "; COMMAMD=" . $cmd);

        }


}
?>