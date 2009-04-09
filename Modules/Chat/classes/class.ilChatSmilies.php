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
* Class ilChatSmilies
* 
* @author Jan Posselt <jposselt@databay.de> 
* @version $Id$
*
*/
class ilChatSmilies {
	/**
	 *	create table and sequence 
	 */
	private static function _setupDatabase() {
		global $ilDB;
		$fields = array(
			'smiley_id' => array(
				'type' => 'integer',
				'length' => 4,
			),
			'smiley_keywords' => array(
				'type' => 'text',
				'length' => 100,
			),
			'smiley_path' => array(
				'type' => 'text',
				'length' => 200,
			)
		);
		
		$ilDB->dropTable("chat_smilies");
		$ilDB->dropTable("chat_smilies_seq");
		
		$ilDB->createTable('chat_smilies', $fields);
		$ilDB->addPrimaryKey('chat_smilies', array('smiley_id'));
		$ilDB->createSequence('chat_smilies');
	}

	/**
	 *	insert default smiley set 
	 */
	private static function _insertDefaultValues() {
		global $ilDB;
		$values = array (
			array("icon_smile.gif", ":)\n:-)\n:smile:"),
			array("icon_wink.gif", ";)\n;-)\n:wink:"),
			array("icon_laugh.gif", ":D\n:-D\n:laugh:\n:grin:\n:biggrin:"),
			array("icon_sad.gif", ":(\n:-(\n:sad:"),
			array("icon_shocked.gif", ":o\n:-o\n:shocked:"),
			array("icon_tongue.gif", ":p\n:-p\n:tongue:"),
			array("icon_cool.gif", ":cool:"),
			array("icon_eek.gif", ":eek:"),
			array("icon_angry.gif", ":||\n:-||\n:angry:"),
			array("icon_flush.gif", ":flush:"),
			array("icon_idea.gif", ":idea:"),
			array("icon_thumbup.gif", ":thumbup:"),
			array("icon_thumbdown.gif", ":thumbdown:"),
		);
		
		$stmt = $ilDB->prepare("
			INSERT INTO chat_smilies (smiley_id, smiley_keywords, smiley_path)
			VALUES (?, ?, ?)",
			array(
				"integer", "text", "text"		
			)
		);
		
		foreach($values as $val) {
			$row = array(
				$ilDB->nextID("chat_smilies"),
				$val[1],
				$val[0]
			);
			$stmt->execute($row);
		}
	}
	
	/**
	 *	setup directory
	 */
	private static function _setupFolder() {
		
		$path = ilUtil::getWebspaceDir().'/chat/smilies';
		
		if (!is_dir($path)) {
			mkdir($path, 0755, true);
		}
	}
	
	/**
	 * @return string	path to smilies
	 */
	public static function _getSmileyDir() {
		return ilUtil::getWebspaceDir().'/chat/smilies';
	}
	
	/**
	 *	performs initial setup (db, dirs, default data) 
	 */
	public static function _initial() {
		self::_setupDatabase();
		self::_insertDefaultValues();
		self::_setupFolder();
	}
	
	/**
	 *	@return array
	 */
	
	public static function _getSmilies() {
		global $ilDB;
		
		$res = $ilDB->query("SELECT smiley_id, smiley_keywords, smiley_path FROM chat_smilies");
		$result = array();
		
		for ($i = 0; $i < $res->numRows(); $i++) {
			$tmp = $res->fetchRow();
			$result[] = array(
				"smiley_id" => $tmp[0],
				"smiley_keywords" => $tmp[1],
				"smiley_path" => $tmp[2],
				"smiley_fullpath" => ilUtil::getWebspaceDir().'/chat/smilies/'.$tmp[2]
			);
		}
		return $result;		
	}
	
	/**
	 * lookup and return smiley with id
	 * throws exception if id is not found
	 * 
	 * @return array
	 */
	public static function _getSmiley($a_id) {
		global $ilDB;
		
		$res = $ilDB->queryF("
			SELECT smiley_id, smiley_keywords, smiley_path
			FROM chat_smilies
			WHERE smiley_id = %s 
		", array('integer'), array($a_id));
		
		if ($res->numRows()) {
			$tmp = $res->fetchRow();
			$result = array(
				"smiley_id" => $tmp[0],
				"smiley_keywords" => $tmp[1],
				"smiley_path" => $tmp[2],
				"smiley_fullpath" => ilUtil::getWebspaceDir().'/chat/smilies/'.$tmp[2]
			);
			return $result;
		}
		throw new Exception('smiley with id $a_id not found');
	}
	
	public static function _getSmiliesById($ids = array()) {
		global $ilDB;
		
		if (!count($ids)) return;
		
		$sql = "SELECT smiley_id, smiley_keywords, smiley_path FROM chat_smilies WHERE ";
		
		$sql_parts = array();
		foreach($ids as $id) {
			$sql_parts[] .= "smiley_id = " . $ilDB->quote($id, "integer");
		}
		
		$sql .= join(" OR ", $sql_parts);
		
		$res = $ilDB->query($sql);
		$result = array();
		for($i = 0; $i < $res->numRows(); $i++) {
			$tmp = $res->fetchRow();
			$result[] = array(
				"smiley_id" => $tmp[0],
				"smiley_keywords" => $tmp[1],
				"smiley_path" => $tmp[2],
				"smiley_fullpath" => ilUtil::getWebspaceDir().'/chat/smilies/'.$tmp[2]
			);
		}
		return $result;
	}
	
	public static function _deleteMultipleSmilies($ids = array()) {
		global $ilDB;
		$smilies = self::_getSmiliesById($ids);
		if (count($smilies) <= 0)
			return;
			
		$sql_parts = array();
		
		foreach($smilies as $s) {
			unlink($s["smiley_fullpath"]);
			$sql_parts[] = "smiley_id = " . $ilDB->quote($s["smiley_id"],'integer');
		}
		
		$ilDB->manipulate("DELETE FROM chat_smilies WHERE " . join(" OR ", $sql_parts) );
	}
	
	public static function _updateSmiley($data) {
		global $ilDB;
		
		$ilDB->manipulateF(
			"UPDATE chat_smilies
			SET smiley_keywords = %s
			WHERE
				smiley_id = %s",
			array('text', 'integer'),
			array($data["smiley_keywords"], $data["smiley_id"])
		);
		
		if ($data["smiley_path"]) {
			$sm = self::_getSmiley($data["smiley_id"]);
			unlink($sm["smiley_fullpath"]);
			$ilDB->manipulateF(
				"UPDATE chat_smilies
				SET smiley_path = %s
				WHERE
					smiley_id = %s",
				array('text', 'integer'),
				array($data["smiley_path"], $data["smiley_id"])
			);			
		}
		
	}
	
	public static function _getSmiliesBasePath() {
		return ilUtil::getWebspaceDir().'/chat/smilies/';
	}
	
	public static function _deleteSmiley($a_id) {
		global $ilDB;
		
		try {
			$smiley = self::_getSmiley($a_id);
			$path = ilUtil::getWebspaceDir().'/chat/smilies/'.$smiley["smiley_path"];
			if (is_file($path))
				unlink($path);
				
			$ilDB->manipulateF(
				"DELETE FROM chat_smilies
				WHERE
					smiley_id = %s",
				array('integer'),
				array($a_id)
			);
		}
		catch(Exception $e) {
			
		}
	}
	
	public static function _storeSmiley($keywords, $path) {
		global $ilDB;
		
		$stmt = $ilDB->prepare("
			INSERT INTO chat_smilies (smiley_id, smiley_keywords, smiley_path)
			VALUES (?, ?, ?)",
			array(
				"integer", "text", "text"		
			)
		);
		$row = array(
			$ilDB->nextID("chat_smilies"),
			$keywords,
			$path
		);
		$stmt->execute($row);
	}
	
	public static function _prepareKeywords($words) {
		$keywordscheck = true;
		
		// check keywords
		$keywords_unchecked = explode("\n", $words);
		if (count($keywords_unchecked) <= 0)
			$keywordscheck = false;
		
		if ($keywordscheck) {
			$keywords = array();
			foreach($keywords_unchecked as $word) {
				if (trim($word))
					$keywords[] = trim($word);
			}
		}
		
		if ($keywordscheck && count($keywords) <= 0)
			$keywordscheck = false;
		
		if ($keywordscheck)
			return $keywords;
		else
			return array();
	}
	
	public static function _parseString($str) {
		global $ilDB;
		
		$q = $ilDB->query(
			"SELECT smiley_keywords, smiley_path
			FROM chat_smilies"
		);
		
		$ar_search = array();
		$ar_replace = array();
		
		$ostr = "";
		for($i = 0; $i < $q->numRows(); $i++) {
			$row = $q->fetchRow();
			$keywords = explode("\n", $row[0]);

			for ($x = 0; $x < count($keywords); $x++) {
				$ar_search[] = $keywords[$x];

				$tpl = new ilTemplate("tpl.chat_smiley_line.html", true, true, "Modules/Chat");				
				$tpl->setVariable("SMILEY_PATH", ilUtil::getHtmlPath(self::_getSmiliesBasePath().$row[1]));
				$tpl->setVariable("SMILEY_ALT", $keywords[$x]);
				$tpl->parseCurrentBlock();
				
				$ar_replace[] = $tpl->get();
				
			}
		}

		$str = str_replace($ar_search, $ar_replace, $str);
		return $str;
	}
}

?>