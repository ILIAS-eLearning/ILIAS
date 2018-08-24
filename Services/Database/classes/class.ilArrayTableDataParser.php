<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilArrayTableDataParser
{
	protected $dir = null;
	
	protected $value = '';
	
	public function __construct($data_dir)
	{
		$this->dir = $data_dir;
	}
	
	public function startParsing()
	{
		global $ilDB,$ilLog;
		
		if(!$dp = opendir($this->dir))
		{
			$ilLog->write(__METHOD__.': Cannot open data directory: '.$this->dir);
			return false;
		}
	
		$ilLog->write(__METHOD__.': Reading table data from: '.$this->dir);
		while (false !== ($file = readdir($dp))) 
		{
			$ilLog->write(__METHOD__.': Handling file: '.$file);
			if(substr($file, -5) != '.data')
			{
				$ilLog->write(__METHOD__.': Ignoring file: '.$file);
				continue;
			}
			
			$content = file_get_contents($this->dir.DIRECTORY_SEPARATOR.$file);

			$ilLog->write(__METHOD__.': Reading inserts of '.$this->dir.'/'.$file);
			$content = unserialize($content);

			if(!is_array($content))
			{
				$ilLog->write(__METHOD__.': No entries found in '.$this->dir.'/'.$file);
				continue;
			}

			foreach($content as $table => $rows)
			{
				foreach($rows as $row)
				{
					$ilDB->insert($table,$row);
				}
			}
			if(function_exists('memory_get_usage'))
			{
				$ilLog->write(__METHOD__.': Memory usage '.memory_get_usage(true));
			}
		}
		fclose($dp);
	}

}
?>