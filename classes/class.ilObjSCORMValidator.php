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
		@author Romeo Kienzler contact@kienzler.biz
		@company 21 LearnLine AG info@21ll.com
	*/
class ilObjSCORMValidator {
		var $dir,$flag,$summary;

		function validateXML($file) {
			$error = system("/usr/local/java/bin/java -jar /opt/ilias/www/htdocs/ilias3/java/vali.jar ".$file." 2>&1");
			if (!$error) {
				$this->summary .= $file." is ok!<br>";
			} else {
				$this->summary .= $file." is not valid!<br>Errormessage is:<br>".$error;
			}
		}
																     
		function searchDir($dir) {
			if (is_dir($dir)) {
				if ($dh = opendir($dir)) {
					while (($file = readdir($dh)) !== false) {
						if (!eregi("^[\.]{1,2}",$file)) {
							//2DO FIXME regex machen dass nur . und .. erkannt werden und nicht .lala. oder so
							if (is_dir($dir.$file)) {
								// This is commented because subdirecories of my scromexamples contain xml files which aren't valid!
								//$this->searchDir($dir.$file."/");
							}
							if (eregi("(\.xml)$",$file)) {
								$this->validateXML($dir.$file);
							}
						}
					}
				}
				closedir($dh);
			}
		}
																     
		function ilObjSCORMValidator($directory) {
			$this->dir = $directory.'/';
		}

		function validate() {
			$this->searchDir($this->dir);
			return $this->flag;
		}

		function getSummary() {
			return $this->summary;
		}
}

?>
