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
	var $width;
	var $height;
	var $parameters;
	/*
	var $mime;
	var $file;
	var $caption;*/
	var $halign;

	var $dom;
	var $hier_id;
	var $node;
	var $mob_node;

	/**
	* Constructor
	* @access	public
	*/
	function ilMediaObject($a_id = 0)
	{

		parent::ilObjMediaObject($a_id);

		$this->is_alias = false;
		$this->parameters = array();

		if($a_id != 0)
		{
			$this->read();
		}
	}

	/**
	* read media object data from db
	*/
	function read()
	{
		// read media_object record
		$query = "SELECT * FROM media_object WHERE id = '".$this->getId()."'";
		$mob_set = $this->ilias->db->query($query);
		$mob_rec = $mob_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setWidth($mob_rec["width"]);
		$this->setHeight($mob_rec["height"]);
		$this->setHAlign($mob_rec["halign"]);

		// read mob parameters
		$query = "SELECT * FROM mob_parameter WHERE mob_id = '".$this->getId()."'";
		$par_set = $this->ilias->db->query($query);
		while ($par_rec = $par_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->parameters[$par_rec["name"]] = $par_rec["value"];
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
	* get width
	*/
	function getWidth()
	{
		return $this->width;
	}

	/**
	* set width
	*/
	function setWidth($a_width)
	{
		$this->width = $a_width;
	}

	/**
	* get height
	*/
	function getHeight()
	{
		return $this->height;
	}

	/**
	* set height
	*/
	function setHeight($a_height)
	{
		$this->height = $a_height;
	}

	/*
	function setMime($a_mime)
	{
		$this->mime = $a_mime;
	}

	function getMime()
	{
		return $this->mime;
	}

	function setFile($a_file)
	{
		$this->file = $a_file;
	}

	function getFile()
	{
		return $this->file;
	}

	function setCaption($a_caption)
	{
		$this->caption = $a_caption;
	}

	function getCaption()
	{
		return $this->caption;
	}*/

	function setHAlign($a_halign)
	{
		$this->halign = $a_halign;
	}

	function getHAlign()
	{
		return $this->halign;
	}


	/**
	* set parameter
	*/
	function setParameter($a_name, $a_value)
	{
		$this->parameters[$a_name] = $a_value;
	}

	/**
	* get all parameters
	*/
	function getParameters()
	{
		return $this->parameters;
	}

	/**
	* get a single parameter
	*/
	function getParameter($a_name)
	{
		return $this->parameter[$a_name];
	}

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
		$query = "INSERT INTO media_object (id, width, height, halign) VALUES ".
			"('".$this->getId()."','".$this->getWidth()."','".$this->getHeight().
			"','".$this->getHAlign()."')";
		$this->ilias->db->query($query);

		// create mob parameters
		foreach($this->parameters as $name => $value)
		{
			$query = "INSERT INTO mob_parameter(mob_id, name, value) VALUES ".
				"('".$this->getId()."', '$name','$value')";
			$this->ilias->db->query($query);
		}
	}

	/**
	* update media object in db
	*/
	function update()
	{
		// update mob
		parent::update();
		$query = "UPDATE media_object SET ".
			" width = '".$this->getWidth."',".
			" height = '".$this->getHeight."',".
			" halign = '".$this->getHAlign."' ".
			" WHERE id = '".$this->getId()."'";
		$this->ilias->db->query($query);
//echo "<b>".$query."</b>";

		// update mob parameters
		$query = "DELETE FROM mob_parameter WHERE mob_id = '".$this->getId()."'";
		$this->ilias->db->query($query);
		foreach($this->parameters as $name => $value)
		{
			$query = "INSERT INTO mob_parameter(mob_id, name, value) VALUES ".
				"('".$this->getId()."', '$name','$value')";
			$this->ilias->db->query($query);
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
				$xml .= "<Layout Width=\"".$this->getWidth()."\" Height=\"".$this->getHeight()."\"/>\n";
				$parameters = $this->getParameters();
				foreach ($parameters as $name => $value)
				{
					$xml .= "<Parameter Name=\"$name\" Value=\"$value\"/>\n";
				}
				break;

			// for output we need technical sections of meta data
			case IL_MODE_OUTPUT:
				// get first technical section
				$meta =& $this->getMetaData();
				$xml = "<MediaObject Id=\"".$this->getId()."\">\n";
//echo "count techs2:".count($meta->technicals).":<br>";
				$technical =& $meta->getTechnicalSection(1);
//echo "<b>wanna technical</b>".$this->getId();
				if ($technical != false)
				{
//echo "<b>got technical</b>".$this->getId();
					$xml .= $technical->getXML();
				}
				$xml .= "<Layout Width=\"".$this->getWidth()."\" Height=\"".$this->getHeight()."\"/>\n";
				$parameters = $this->getParameters();
				foreach ($parameters as $name => $value)
				{
					$xml .= "<Parameter Name=\"$name\" Value=\"$value\"/>\n";
				}
				break;
		}
		$xml .= "</MediaObject>";

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
		$layout_node =& $this->dom->create_element("Layout");
		$layout_node =& $this->mob_node->append_child($layout_node);
		if ($this->getWidth() > 0)
		{
			$layout_node->set_attribute("Width", $this->getWidth());
		}
		if ($this->getHeight() > 0)
		{
			$layout_node->set_attribute("Height", $this->getHeight());
		}
		$layout_node->set_attribute("HorizontalAlign", "Left");
	}


	/**
	* set alignment of mob in dom
	*/
	function setHorizontalAlign($a_align)
	{
		//$this->setHAlign($a_align);		// this is the object, we are setting alias

		// get Layout node
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/Layout";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$layout_node =& $res->nodeset[0];
			$layout_node->set_attribute("HorizontalAlign", $a_align);
		}
	}

	function setAliasWidth($a_width)
	{
		// get Layout node
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/Layout";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$layout_node =& $res->nodeset[0];
			$layout_node->set_attribute("Width", $a_width);
		}
	}

	function getAliasWidth()
	{
		// get Layout node
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/Layout";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$layout_node =& $res->nodeset[0];
			return $layout_node->get_attribute("Width");
		}
	}

	function setAliasHeight($a_height)
	{
		// get Layout node
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/Layout";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$layout_node =& $res->nodeset[0];
			$layout_node->set_attribute("Height", $a_height);
		}
	}

	function getAliasHeight()
	{
		// get Layout node
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/Layout";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$layout_node =& $res->nodeset[0];
			return $layout_node->get_attribute("Height");
		}
	}

	function setAliasCaption($a_caption)
	{
		// get Layout node
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/Parameter[@Name='caption']";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$par_node =& $res->nodeset[0];
			$par_node->set_attribute("Value", $a_caption);
		}
		else if (count($res->nodeset) == 0)
		{
			$xpc = xpath_new_context($this->dom);
			$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject";
			$res =& xpath_eval($xpc, $path);
			if (count($res->nodeset) == 1)
			{
				$med_node =& $res->nodeset[0];
				$par_node =& $this->dom->create_element("Parameter");
				$par_node =& $med_node->append_child($par_node);
				$par_node->set_attribute("Name", "caption");
				$par_node->set_attribute("Value", $a_caption);
			}
		}
	}


	function getAliasCaption()
	{
		// get Layout node
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/Parameter[@Name='caption']";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$par_node =& $res->nodeset[0];
			return $par_node->get_attribute("Value");
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
