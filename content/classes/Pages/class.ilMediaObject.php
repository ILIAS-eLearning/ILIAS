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

define ("IL_MODE_ALIAS", 1);
define ("IL_MODE_OUTPUT", 2);
define ("IL_MODE_FULL", 3);

require_once("classes/class.ilObjMediaObject.php");
require_once("content/classes/Pages/class.ilMediaItem.php");

/**
* Class ilMediaObject
*
* Todo: this class must be integrated with group/folder handling
*
* ILIAS Media Object
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilMediaObject extends ilObjMediaObject
{
	var $is_alias;
	var $origin_id;
	var $id;

	var $dom;
	var $hier_id;
	var $node;
	var $mob_node;
	var $media_items;

	/**
	* Constructor
	* @access	public
	*/
	function ilMediaObject($a_id = 0)
	{
		parent::ilObjMediaObject($a_id);

		$this->is_alias = false;
		$this->media_items = array();

		if($a_id != 0)
		{
			$this->read();
		}
	}

	function addMediaItem(&$a_item)
	{
		$this->media_items[] =& $a_item;
	}

	function &getMediaItems()
	{
		return $this->media_items;
	}

	function &getMediaItem($a_purpose)
	{
		for($i=0; $i<count($this->media_items); $i++)
		{
			if($this->media_items[$i]->getPurpose() == $a_purpose)
			{
				return $this->media_items[$i];
			}
		}
		return false;
	}

	function getMediaItemNr($a_purpose)
	{
		for($i=0; $i<count($this->media_items); $i++)
		{
			if($this->media_items[$i]->getPurpose() == $a_purpose)
			{
				return $i + 1;
			}
		}
		return false;
	}

	function hasFullscreenItem()
	{
		if(is_object($this->getMediaItem("Fullscreen")))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* read media object data from db
	*/
	function read()
	{
		// read media_object record
		$query = "SELECT * FROM media_item WHERE mob_id = '".$this->getId()."' ".
			"ORDER BY nr";
		$item_set = $this->ilias->db->query($query);
		while ($item_rec = $item_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$media_item =& new ilMediaItem();

			$media_item->setLocation($item_rec["location"]);
			$media_item->setLocationType($item_rec["location_type"]);
			$media_item->setFormat($item_rec["format"]);
			$media_item->setWidth($item_rec["width"]);
			$media_item->setHeight($item_rec["height"]);
			$media_item->setHAlign($item_rec["halign"]);
			$media_item->setCaption($item_rec["caption"]);
			$media_item->setPurpose($item_rec["purpose"]);

			$query = "SELECT * FROM mob_parameter WHERE med_item_id = '".
				$item_rec["id"]."'";
			$par_set = $this->ilias->db->query($query);
			while ($par_rec = $par_set->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$media_item->setParameter($par_rec["name"], $par_rec["value"]);
			}

			// todo: get mapareas

			$this->addMediaItem($media_item);
		}

		// get meta data
		$this->meta_data =& new ilMetaData($this->getType(), $this->getId());
	}

	/**
	* set id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	function getId()
	{
		return $this->id;
	}

	/**
	* set wether page object is an alias
	*/
	function setAlias($a_is_alias)
	{
		$this->is_alias = $a_is_alias;
	}

	function isAlias()
	{
		return $this->is_alias;
	}

	function setOriginID($a_id)
	{
		return $this->origin_id = $a_id;
	}

	function getOriginID()
	{
		return $this->origin_id;
	}

	/*
	function getimportId()
	{
		return $this->meta_data->getImportIdentifierEntryID();
	}*/


	/**
	* get import id
	*/
	function getImportId()
	{
		if($this->isAlias())
		{
//echo "getting import id for mob alias:".$this->getOriginId().":<br>";
			return $this->getOriginId();
		}
		else
		{
//echo "getting import id for mob:".$this->meta_data->getImportIdentifierEntryID().":<br>";
			return $this->meta_data->getImportIdentifierEntryID();
		}
	}

	/**
	* create media object in db
	*/
	function create()
	{
		// create mob
		parent::create();

		$media_items =& $this->getMediaItems();
		for($i=0; $i<count($media_items); $i++)
		{
			$item =& $media_items[$i];
			$query = "INSERT INTO media_item (mob_id, purpose, location, ".
				"location_type, format, width, ".
				"height, halign, caption, nr) VALUES ".
				"('".$this->getId()."',".
				"'".$item->getPurpose()."','".$item->getLocation()."','".
				$item->getLocationType()."','".$item->getFormat()."','".
				$item->getWidth()."','".$item->getHeight()."','".$item->getHAlign().
				"','".$item->getCaption()."','".($i+1)."')";
			$this->ilias->db->query($query);
//echo "create_mob:$query:<br>";
			$item_id = getLastInsertId();

			// create mob parameters
			$params = $item->getParameters();
			foreach($params as $name => $value)
			{
				$query = "INSERT INTO mob_parameter(med_item_id, name, value) VALUES ".
					"('".$item_id."', '$name','$value')";
				$this->ilias->db->query($query);
			}
		}

	}

	/**
	* update media object in db
	*/
	function update()
	{
		// update mob
		parent::update();

		// delete media parameter
		$query = "SELECT * FROM media_item WHERE mob_id = '".$this->getId()."'";
		$item_set = $this->ilias->db->query($query);
		while ($item_rec = $item_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$query = "DELETE FROM mob_parameter WHERE med_item_id = '".$item_rec["id"]."'";
			$this->ilias->db->query($query);
		}

		// delete media items
		$query = "DELETE FROM media_item WHERE mob_id = '".$this->getId()."'";
		$this->ilias->db->query($query);

		// iterate all items
		$media_items =& $this->getMediaItems();
		for($i=0; $i<count($media_items); $i++)
		{
			$item =& $media_items[$i];
//echo "<b>".$query."</b>";

			// create item
			$query = "INSERT INTO media_item (mob_id, purpose, location, ".
				"location_type, format, width, ".
				"height, halign, caption, nr) VALUES ".
				"('".$this->getId()."',".
				"'".$item->getPurpose()."','".$item->getLocation()."','".
				$item->getLocationType()."','".$item->getFormat()."','".
				$item->getWidth().
				"','".$item->getHeight()."','".$item->getHAlign().
				"','".$item->getCaption()."','".($i+1)."')";
			$this->ilias->db->query($query);

			$item_id = getLastInsertId();

			// create parameters
			$params = $item->getParameters();
			foreach($params as $name => $value)
			{
				$query = "INSERT INTO mob_parameter(med_item_id, name, value) VALUES ".
					"('".$item_id."', '$name','$value')";
				$this->ilias->db->query($query);
			}

		}
	}

	/**
	* get MediaObject XLM Tag
	*  @param	int		$a_mode		IL_MODE_ALIAS | IL_MODE_OUTPUT | IL_MODE_FULL
	*/
	function getXML($a_mode = IL_MODE_FULL)
	{
		// TODO: full implementation of all parameters

		switch ($a_mode)
		{
			case IL_MODE_ALIAS:
				$xml = "<MediaObject>\n";
				$xml .= "<MediaAlias OriginId=\"".$this->getId()."\"/>\n";
				$media_items =& $this->getMediaItems();
//echo "MediaItems:".count($media_items).":<br>";
				for($i=0; $i<count($media_items); $i++)
				{
					$item =& $media_items[$i];
					$xml .= "<MediaAliasItem Purpose=\"".$item->getPurpose()."\">";

					// Layout
					$width = ($item->getWidth() != "")
						? "Width=\"".$item->getWidth()."\""
						: "";
					$height = ($item->getHeight() != "")
						? "Height=\"".$item->getHeight()."\""
						: "";
					$halign = ($item->getHAlign() != "")
						? "HorizontalAlign=\"".$item->getHAlign()."\""
						: "";
					$xml .= "<Layout $width $height $halign />\n";

					// Caption
					if ($item->getCaption() != "")
					{
						$xml .= "<Caption Align=\"bottom\">".
							$item->getCaption()."</Caption>\n";
					}

					// Parameter
					$parameters = $item->getParameters();
					foreach ($parameters as $name => $value)
					{
						$xml .= "<Parameter Name=\"$name\" Value=\"$value\"/>\n";
					}
					$xml .= "</MediaAliasItem>";
				}
				break;

			// for output we need technical sections of meta data
			case IL_MODE_OUTPUT:
				// get first technical section
//echo "ilMediaObject::getXML:getMetaData:id:".$this->getId().":<br>";
				$meta =& $this->getMetaData();
				$xml = "<MediaObject Id=\"".$this->getId()."\">\n";
//echo "count techs2:".count($meta->technicals).":<br>";
				/*
				$technical =& $meta->getTechnicalSection(1);
//echo "<b>wanna technical</b>".$this->getId();
				if ($technical != false)
				{
//echo "<b>got technical</b>".$this->getId();
					$xml .= $technical->getXML();
				}*/
				$media_items =& $this->getMediaItems();
				for($i=0; $i<count($media_items); $i++)
				{
					$item =& $media_items[$i];
					$xml .= "<MediaItem Purpose=\"".$item->getPurpose()."\">";

					// Location
					$xml.= "<Location Type=\"".$item->getLocationType()."\">".
						$item->getLocation()."</Location>";

					// Format
					$xml.= "<Format>".$item->getFormat()."</Format>";

					// Layout
					$width = ($item->getWidth() != "")
						? "Width=\"".$item->getWidth()."\""
						: "";
					$height = ($item->getHeight() != "")
						? "Height=\"".$item->getHeight()."\""
						: "";
					$halign = ($item->getHAlign() != "")
						? "HorizontalAlign=\"".$item->getHAlign()."\""
						: "";
					$xml .= "<Layout $width $height $halign />\n";

					// Caption
					if ($item->getCaption() != "")
					{
						$xml .= "<Caption Align=\"bottom\">".
							$item->getCaption()."</Caption>\n";
					}

					// Parameter
					$parameters = $item->getParameters();
					foreach ($parameters as $name => $value)
					{
						$xml .= "<Parameter Name=\"$name\" Value=\"$value\"/>\n";
					}
					$xml .= "</MediaItem>";
				}
				break;
		}
		$xml .= "</MediaObject>";
//echo "MEDIAALIAS:<br>".htmlentities($xml)."<br><br>";
		return $xml;
	}

	//////
	// EDIT METHODS: these methods act on the media alias in the dom
	//////

	/**
	* set dom object
	*/
	function setDom(&$a_dom)
	{
		$this->dom =& $a_dom;
	}

	/**
	* set PageContent node
	*/
	function setNode($a_node)
	{
		$this->node =& $a_node;							// page content node
		$this->mob_node =& $a_node->first_child();			// MediaObject node
	}

	/**
	* get PageContent node
	*/
	function &getNode()
	{
		return $this->node;
	}

	/**
	* set hierarchical edit id
	*/
	function setHierId($a_hier_id)
	{
		$this->hier_id = $a_hier_id;
	}

	function createAlias(&$a_pg_obj, $a_hier_id)
	{
		$this->node =& $this->dom->create_element("PageContent");
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
		$this->mob_node =& $this->dom->create_element("MediaObject");
		$this->mob_node =& $this->node->append_child($this->mob_node);
		$this->mal_node =& $this->dom->create_element("MediaAlias");
		$this->mal_node =& $this->mob_node->append_child($this->mal_node);
		$this->mal_node->set_attribute("OriginId", $this->getId());

		// standard view
		$item_node =& $this->dom->create_element("MediaAliasItem");
		$item_node =& $this->mob_node->append_child($item_node);
		$item_node->set_attribute("Purpose", "Standard");
		$media_item =& $this->getMediaItem("Standard");

		$layout_node =& $this->dom->create_element("Layout");
		$layout_node =& $item_node->append_child($layout_node);
		if ($media_item->getWidth() > 0)
		{
			$layout_node->set_attribute("Width", $media_item->getWidth());
		}
		if ($media_item->getHeight() > 0)
		{
			$layout_node->set_attribute("Height", $media_item->getHeight());
		}
		$layout_node->set_attribute("HorizontalAlign", "Left");

		// caption
		if ($media_item->getCaption() != "")
		{
			$cap_node =& $this->dom->create_element("Caption");
			$cap_node =& $item_node->append_child($cap_node);
			$cap_node->set_attribute("Align", "bottom");
			$cap_node->set_content($media_item->getCaption());
		}

		// fullscreen view
		$fullscreen_item =& $this->getMediaItem("Fullscreen");
		if (is_object($fullscreen_item))
		{
			$item_node =& $this->dom->create_element("MediaAliasItem");
			$item_node =& $this->mob_node->append_child($item_node);
			$item_node->set_attribute("Purpose", "Fullscreen");

			// width and height
			$layout_node =& $this->dom->create_element("Layout");
			$layout_node =& $item_node->append_child($layout_node);
			if ($fullscreen_item->getWidth() > 0)
			{
				$layout_node->set_attribute("Width", $fullscreen_item->getWidth());
			}
			if ($fullscreen_item->getHeight() > 0)
			{
				$layout_node->set_attribute("Height", $fullscreen_item->getHeight());
			}

			// caption
			if ($media_item->getCaption() != "")
			{
				$cap_node =& $this->dom->create_element("Caption");
				$cap_node =& $item_node->append_child($cap_node);
				$cap_node->set_attribute("Align", "bottom");
				$cap_node->set_content($media_item->getCaption());
			}

		}
	}


	/**
	* get mime type for file
	*
	* @param	string		$a_file		file name
	* @return	string					mime type
	* static
	*/
	function getMimeType ($a_file)
	{
		// check if mimetype detection enabled in php.ini
		$set = ini_get("mime_magic.magicfile");

		// get mimetype
		if ($set <> "")
		{
			$mime = @mime_content_type($a_file);
		}

		if (empty($mime))
		{
			$path = pathinfo($a_file);
			$ext = ".".strtolower($path["extension"]);

			/**
			* map of mimetypes.py from python.org (there was no author mentioned in the file)
			*/
			$types_map = ilMediaObject::getExt2MimeMap();
			$mime = $types_map[$ext];
		}

		// set default if mimetype detection failed or not possible (e.g. remote file)
		if (empty($mime))
		{
			$mime = "application/octet-stream";
		}

		return $mime;
	}


	/**
	* get file extension to mime type map
	*/
	function getExt2MimeMap()
	{
		$types_map = array (
			'.a'      => 'application/octet-stream',
			'.ai'     => 'application/postscript',
			'.aif'    => 'audio/x-aiff',
			'.aifc'   => 'audio/x-aiff',
			'.aiff'   => 'audio/x-aiff',
			'.asd'    => 'application/astound',
			'.asn'    => 'application/astound',
			'.au'     => 'audio/basic',
			'.avi'    => 'video/x-msvideo',
			'.bat'    => 'text/plain',
			'.bcpio'  => 'application/x-bcpio',
			'.bin'    => 'application/octet-stream',
			'.bmp'    => 'image/x-ms-bmp',
			'.c'      => 'text/plain',
			'.cdf'    => 'application/x-cdf',
			'.class'  => 'application/octet-stream',
			'.com'    => 'application/octet-stream',
			'.cpio'   => 'application/x-cpio',
			'.csh'    => 'application/x-csh',
			'.css'    => 'text/css',
			'.csv'    => 'text/comma-separated-values',
			'.dcr'    => 'application/x-director',
			'.dir'    => 'application/x-director',
			'.dll'    => 'application/octet-stream',
			'.doc'    => 'application/msword',
			'.dot'    => 'application/msword',
			'.dvi'    => 'application/x-dvi',
			'.dwg'    => 'application/acad',
			'.dxf'    => 'application/dxf',
			'.dxr'    => 'application/x-director',
			'.eml'    => 'message/rfc822',
			'.eps'    => 'application/postscript',
			'.etx'    => 'text/x-setext',
			'.exe'    => 'application/octet-stream',
			'.gif'    => 'image/gif',
			'.gtar'   => 'application/x-gtar',
			'.gz'     => 'application/gzip',
			'.h'      => 'text/plain',
			'.hdf'    => 'application/x-hdf',
			'.htm'    => 'text/html',
			'.html'   => 'text/html',
			'.ief'    => 'image/ief',
			'.iff'    => 'image/iff',
			'.jpe'    => 'image/jpeg',
			'.jpeg'   => 'image/jpeg',
			'.jpg'    => 'image/jpeg',
			'.js'     => 'application/x-javascript',
			'.ksh'    => 'text/plain',
			'.latex'  => 'application/x-latex',
			'.m1v'    => 'video/mpeg',
			'.man'    => 'application/x-troff-man',
			'.me'     => 'application/x-troff-me',
			'.mht'    => 'message/rfc822',
			'.mhtml'  => 'message/rfc822',
			'.mid'    => 'audio/x-midi',
			'.midi'   => 'audio/x-midi',
			'.mif'    => 'application/x-mif',
			'.mov'    => 'video/quicktime',
			'.movie'  => 'video/x-sgi-movie',
			'.mp2'    => 'audio/mpeg',
			'.mp3'    => 'audio/mpeg',
			'.mpa'    => 'video/mpeg',
			'.mpe'    => 'video/mpeg',
			'.mpeg'   => 'video/mpeg',
			'.mpg'    => 'video/mpeg',
			'.ms'     => 'application/x-troff-ms',
			'.nc'     => 'application/x-netcdf',
			'.nws'    => 'message/rfc822',
			'.o'      => 'application/octet-stream',
			'.obj'    => 'application/octet-stream',
			'.oda'    => 'application/oda',
			'.p12'    => 'application/x-pkcs12',
			'.p7c'    => 'application/pkcs7-mime',
			'.pbm'    => 'image/x-portable-bitmap',
			'.pdf'    => 'application/pdf',
			'.pfx'    => 'application/x-pkcs12',
			'.pgm'    => 'image/x-portable-graymap',
			'.php'    => 'application/x-httpd-php',
			'.phtml'  => 'application/x-httpd-php',
			'.pl'     => 'text/plain',
			'.png'    => 'image/png',
			'.pnm'    => 'image/x-portable-anymap',
			'.pot'    => 'application/vnd.ms-powerpoint',
			'.ppa'    => 'application/vnd.ms-powerpoint',
			'.ppm'    => 'image/x-portable-pixmap',
			'.pps'    => 'application/vnd.ms-powerpoint',
			'.ppt'    => 'application/vnd.ms-powerpoint',
			'.ps'     => 'application/postscript',
			'.psd'    => 'image/psd',
			'.pwz'    => 'application/vnd.ms-powerpoint',
			'.py'     => 'text/x-python',
			'.pyc'    => 'application/x-python-code',
			'.pyo'    => 'application/x-python-code',
			'.qt'     => 'video/quicktime',
			'.ra'     => 'audio/x-pn-realaudio',
			'.ram'    => 'application/x-pn-realaudio',
			'.ras'    => 'image/x-cmu-raster',
			'.rdf'    => 'application/xml',
			'.rgb'    => 'image/x-rgb',
			'.roff'   => 'application/x-troff',
			'.rpm'    => 'audio/x-pn-realaudio-plugin',
			'.rtf'    => 'application/rtf',
			'.rtx'    => 'text/richtext',
			'.sgm'    => 'text/x-sgml',
			'.sgml'   => 'text/x-sgml',
			'.sh'     => 'application/x-sh',
			'.shar'   => 'application/x-shar',
			'.sit'    => 'application/x-stuffit',
			'.snd'    => 'audio/basic',
			'.so'     => 'application/octet-stream',
			'.spc'    => 'text/x-speech',
			'.src'    => 'application/x-wais-source',
			'.sv4cpio'=> 'application/x-sv4cpio',
			'.sv4crc' => 'application/x-sv4crc',
			'.svg'    => 'image/svg+xml',
			'.swf'    => 'application/x-shockwave-flash',
			'.t'      => 'application/x-troff',
			'.tar'    => 'application/x-tar',
			'.talk'   => 'text/x-speech',
			'.tbk'    => 'application/toolbook',
			'.tcl'    => 'application/x-tcl',
			'.tex'    => 'application/x-tex',
			'.texi'   => 'application/x-texinfo',
			'.texinfo'=> 'application/x-texinfo',
			'.tif'    => 'image/tiff',
			'.tiff'   => 'image/tiff',
			'.tr'     => 'application/x-troff',
			'.tsv'    => 'text/tab-separated-values',
			'.tsp'    => 'application/dsptype',
			'.txt'    => 'text/plain',
			'.ustar'  => 'application',
			'.vcf'    => 'text/x-vcard',
			'.vox'    => 'audio/voxware',
			'.wav'    => 'audio/x-wav',
			'.wiz'    => 'application/msword',
			'.wml'    => 'text/vnd.wap.wml',
			'.wmlc'   => 'application/vnd.wap.wmlc',
			'.wmls'   => 'text/vnd.wap.wmlscript',
			'.wmlsc'  => 'application/vnd.wap.wmlscriptc',
			'.wrl'    => 'x-world/x-vrml',
			'.xbm'    => 'image/x-xbitmap',
			'.xla'    => 'application/msexcel',
			'.xlb'    => 'application/vnd.ms-excel',
			'.xls'    => 'application/msexcel',
			'.xml'    => 'text/xml',
			'.xpm'    => 'image/x-xpixmap',
			'.xsl'    => 'application/xml',
			'.xwd'    => 'image/x-xwindowdump',
			'.zip'    => 'application/zip');

		return $types_map;
	}
}
?>
