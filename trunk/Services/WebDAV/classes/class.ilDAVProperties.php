<?php
// BEGIN WebDAV
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Class ilDAVProperties
*
* Provides support for DAV specific object properties.
* This class encapsulates the database table dav_property.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id: class.ilDAVProperties.php,v 1.0 2005/07/08 12:00:00 wrandelshofer Exp $
*
* @package webdav
*/
class ilDAVProperties
{
	private $table = 'dav_property';
	
	
	/**
	 * Puts a property for the specified DAV object.
	 *
	 * @param ilObjectDAV DAV object for which the property is put
	 * @param string Namespace of the property
	 * @param string Name of the property
	 * @param string Value of the property. Specify null, to remove the property.
	 */
	public function put($objDAV, $namespace, $name, $value)
	{
		//$this->writelog('put ns='.$namespace.' name='.$name.' value='.$value);
		global $ilDB;
		
		$objId = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();
		
		if(isset($value)) 
		{
			$ilDB->replace($this->table,
				array(
					'obj_id'	=> array('integer',$objId),
					'node_id'	=> array('integer',$nodeId),
					'ns'		=> array('text',$namespace),
					'name'		=> array('text',$name)
					),
				array('value'	=> array('clob',$value))
			);
			
			/*			
			$q = 'REPLACE INTO '.$this->table
					.' SET obj_id = '.$ilDB->quote($objId)
					.', node_id = '.$ilDB->quote($nodeId)
					.', ns = '.$ilDB->quote($namespace)
					.', name = '.$ilDB->quote($name)
					.', value = '.$ilDB->quote($value)
					;
			*/
		} 
		else 
		{
			$q = 'DELETE FROM '.$this->table
					.' WHERE obj_id = '.$ilDB->quote($objId,'integer')
					.' AND node_id = '.$ilDB->quote($nodeId,'integer')
					.' AND ns = '.$ilDB->quote($namespace,'text')
					.' AND name = '.$ilDB->quote($name,'text')
					;
			$ilDB->manipulate($q);
		}       
		//$this->writelog('put query='.$q);
		#$r = $ilDB->query($q);
	}
	
	/**
	 * Gets a property from the specified DAV object.
	 *
	 * @param ilObjectDAV DAV object for which the property is put
	 * @param string Namespace of the property
	 * @param string Name of the property
	 * @return string Value of the property. Returns null if the property is empty.
	 */
	public function get($objDAV, $namespace, $name, $value)
	{
		global $ilDB;
		
		$objId = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();
		
		$q = 'SELECT value FROM '.$this->table
				.' WHERE obj_id = '.$ilDB->quote($objId,'integer')
				.' AND node_id ='.$ilDB->quote($nodeId,'integer')
				.' AND ns = '.$ilDB->quote($namespace,'text')
				.' AND name = '.$ilDB->quote($name,'text')
				;       
		$r = $ilDB->query($q);
		if ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$value = $row['value'];
		} else {
			$value = null;
		}
		return $value;
	}
	/**
	 * Gets all properties from the specified DAV object.
	 *
	 * @param ilObjectDAV DAV object for which the property is put
	 * @return array of assicative arrays. Each associative array contains the fields
	 * 'namespace','name' and 'value'.
	 */
	public function getAll($objDAV)
	{
		global $ilDB;
		
		$objId = $objDAV->getObjectId();
		$nodeId = $objDAV->getNodeId();
		
		$q = 'SELECT ns, name, value'
				.' FROM '.$this->table
				.' WHERE obj_id = '.$ilDB->quote($objId,'integer')
				.' AND node_id ='.$ilDB->quote($nodeId,'integer')
				;       
		$r = $ilDB->query($q);
		$result = array();
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$result[] = array(
				'namespace' => $row['ns'],
				'name' => $row['name'],
				'value' => $row['value']
			);
		}
		return $result;
	}
	
	/**
	 * Moves all properties from one dav object to another.
	 * /
	public function move($fromObjDAV, $toObjDAV)
	{
		global $ilDB;
		
		$fromObjId = $fromObjDAV->getObjectId();
		$fromNodeId = $fromObjDAV->getNodeId();
		$toObjId = $toObjDAV->getObjectId();
		$toNodeId = $toObjDAV->getNodeId();
		
		$q = 'UPDATE '.$this->table
			.' SET obj_id = '.$ilDB->quote($toObjId)
			.', node_id = '.$ilDB->quote($toNodeId)
			.' WHERE obj_id = '.$ilDB->quote($fromObjId)
			.' AND node_id ='.$ilDB->quote($toNodeId)
		;       
		$r = $ilDB->query($q);
	}*/
	/**
	 * Copies all properties from one dav object to another.
	 */
	public function copy($fromObjDAV, $toObjDAV)
	{
		global $ilDB;
		
		$fromObjId = $fromObjDAV->getObjectId();
		$fromNodeId = $fromObjDAV->getNodeId();
		$toObjId = $toObjDAV->getObjectId();
		$toNodeId = $toObjDAV->getNodeId();
		
		$q = 'SELECT ns, name, value FROM '.$this->table
				.' WHERE obj_id = '.$ilDB->quote($objId,'integer')
				.' AND node_id ='.$ilDB->quote($nodeId,'integer');
/*				.' FOR UPDATE' */
		$r = $ilDB->query($q);
		$result = array();
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$q2 = 'INSERT INTO '.$this->table
				.' (obj_id, node_id, ns, name, value)'
			 	.' VALUES'
				.'('.$ilDB->quote($row['obj_id'])
				.', '.$ilDB->quote($row['node_id'])
				.', '.$ilDB->quote($row['ns'])
				.', '.$ilDB->quote($row['name'])
				.', '.$ilDB->quote($row['value'])
				.')'
			;
			$r2 = $ilDB->manipulate($q2);
		}
	}
	
	/**
	 * Writes a message to the logfile.,
	 *
	 * @param  message String.
	 * @return void.
	 */
	protected function writelog($message) 
	{
		global $log, $ilias;
		$log->write(
			$ilias->account->getLogin()
			.' DAV ilDAVProperties.'.str_replace("\n",";",$message)
		);
		/*
		if ($this->logFile) 
		{
			$fh = fopen($this->logFile, 'a');
			fwrite($fh, date('Y-m-d h:i:s '));
			fwrite($fh, str_replace("\n",";",$message));
			fwrite($fh, "\n\n");
			fclose($fh);		
		}*/
	}
}
// END WebDAV
?>
