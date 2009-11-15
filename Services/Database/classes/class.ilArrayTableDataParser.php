<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilArrayTableDataParser
{
	protected $file = null;
	protected $xml = null;
	
	protected $value = '';
	
	public function __construct($a_xml)
	{
		$this->file = $a_xml;
	}
	
	public function startParsing()
	{
		global $ilDB,$ilLog;

		$fp = fopen($this->file,'r');
		while(!feof($fp))
		{
			$ilLog->write(__METHOD__.': Reading new line of '.$this->file);
			$buffer = fgets($fp);
			$ilLog->write(__METHOD__.': Line length '.strlen($buffer));
			$content = unserialize($buffer);

			if(!is_array($content))
			{
				fclose($fp);
				$ilLog->write(__METHOD__.': No entries found for '.$this->file);
				if(function_exists('memory_get_usage'))
				{
					$ilLog->write(__METHOD__.': Memory usage '.memory_get_usage(true));
				}
				return false;
			}

			foreach($content as $table => $rows)
			{
				foreach($rows as $row)
				{
					$ilDB->insert($table,$row);
				}
			}
		}
		fclose($fp);
		return true;
	}

}
?>