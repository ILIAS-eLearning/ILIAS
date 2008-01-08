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
* Class for indexing hmtl ,pdf, txt files and htlm Learning modules.
* This indexer is called by cron.php
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/

class ilLuceneIndexer
{
	function ilLuceneIndexer()
	{
		global $ilLog,$ilDB;

		$this->log =& $ilLog;
		$this->db =& $ilDB;
	}

	function index()
	{
		// Todo check in settings which objects should be indexed
		$this->__flushIndex();
		$this->__indexFiles();
		$this->__indexHTLMs();

		return true;
	}

	// PRIVATE
	function __indexFiles()
	{
		include_once('Services/FileSystemStorage/classes/class.ilFileSystemStorage.php');
		
		global $tree;

		$query = "SELECT * FROM file_data ".
			"WHERE file_type IN ('text/plain','application/pdf','text/html')";

		$res = $this->db->query($query);

		$counter = 0;
		$files = array();
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			++$counter;
			$bname = ilUtil::getDataDir();
			$bname .= ("/ilFiles/");
			$bname .= ilFileSystemStorage::_createPathFromId($row->file_id,'file');
			$vname = (sprintf("%03d", $row->version));

			if(is_file($bname.'/'.$vname.'/'.$row->file_name))
			{
				$files[$row->file_id] = $bname.'/'.$vname.'/'.$row->file_name;
			}
			else
			{
				$files[$row->file_id] = $bname.'/'.$row->file_name;
			}
		}
		$this->log->write('Lucene indexer: Found '.$counter.' files for indexing');

		if(count($files))
		{
			// Send files to lucene rpc server
			include_once './Services/Search/classes/Lucene/class.ilLuceneRPCAdapter.php';

			$rpc_adapter =& new ilLuceneRPCAdapter();
			$rpc_adapter->setMode('file');
			$rpc_adapter->setFiles($files);
			if($rpc_adapter->send())
			{
				$this->log->write('Lucene indexer: files sent');
			}
			return true;
		}
	}
	function __indexHTLMs()
	{
		global $ilias;

		$query = "SELECT * FROM object_data WHERE type = 'htlm'";
		$res = $this->db->query($query);
		$counter = 0;
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			++$counter;
			$lms[$row->obj_id] = ILIAS_ABSOLUTE_PATH.'/'.ILIAS_WEB_DIR.'/'.CLIENT_ID.'/lm_data/lm_'.$row->obj_id;

		}
		$this->log->write('Lucene indexer: Found '.$counter.' html learning modules for indexing');

		if(count($lms))
		{
			// Send files to lucene rpc server
			include_once './Services/Search/classes/Lucene/class.ilLuceneRPCAdapter.php';

			$rpc_adapter =& new ilLuceneRPCAdapter();
			$rpc_adapter->setMode('htlm');
			$rpc_adapter->setHTLMs($lms);
			if($rpc_adapter->send())
			{
				$this->log->write('Lucene indexer: files sent');
			}
			return true;
		}
	}
	function __flushIndex()
	{
		include_once './Services/Search/classes/Lucene/class.ilLuceneRPCAdapter.php';

		$rpc_adapter =& new ilLuceneRPCAdapter();
		$rpc_adapter->setMode('flush');
		if($rpc_adapter->send())
		{
			$this->log->write('Lucene indexer: deleted index');
		}
		return true;
	}		
}
?>
