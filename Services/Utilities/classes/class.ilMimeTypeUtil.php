<?php
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
* Mime type determination.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilMimeTypeUtil
{
	/**
	* Get Mime type
	*
	* @param	string		full path of file (incl. filename)
	* @param	string		file name (must be provided if no full path is given)
	* @param	string		mime type that will be used initially. Provide
	*						any mime type headers
	*/
	static function getMimeType($a_file = "", $a_filename = "", $a_mime = "")
	{
		global $ilLog;
		
//$ilLog->write("getMimeType-".$a_file."-".$a_filename."-".$a_mime."-");
		
		$mime = $a_mime;
//echo "<br>-".$a_file."-".$a_filename."-".$a_mime."-";
		// check if mimetype detection enabled in php.ini
		$set = ini_get("mime_magic.magicfile");
		// get mimetype
		if ($set <> "")
		{
			if ($mime == "" && is_file($a_file))
			{
				$mime = @mime_content_type($a_file);
			}
		}

		// Firefox browser assigns 'application/x-pdf' to PDF files, but
		// it can only handle them if the have the mime-type 'application/pdf'.
		if ($mime == 'application/x-pdf')
		{
			$mime = 'application/pdf';
		}

		// typical standard cases for wrong detection should be
		// "overwritten" by suffic mime map
		// text/plain, so we make our own detection in this case, too
		// for x-zip, see bug #6102
		if ($mime == "" || trim(strtolower($mime)) == "text/plain" ||
			trim(strtolower($mime)) == "application/octet-stream" ||
			trim(strtolower($mime)) == "application/force-download" ||
			trim(strtolower($mime)) == "application/x-zip" ||
			trim(strtolower($mime)) == "text/xml" ||
			trim(strtolower($mime)) == "text/html")
		{
			if ($a_filename != "")	// first check the file name provided
			{
				$path = pathinfo($a_filename);
				$ext = ".".strtolower($path["extension"]);
			}
			else if ($a_file != "")		// check if (full path) file has been provided
			{
				$path = pathinfo($a_file);
				$ext = ".".strtolower($path["extension"]);
			}

			$types_map = ilMimeTypeUtil::getExt2MimeMap();
			if ($types_map[$ext] != "")		// if we find something in our map, use it
			{
				$mime = $types_map[$ext];
			}
		}

		// set default if mimetype detection failed or not possible (e.g. remote file)
		if ($mime == "")
		{
			$mime = "application/octet-stream";
		}

//$ilLog->write("---returning:".$mime.":");
//$ilLog->logStack();

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
			'.asf'    => 'video/x-ms-asf',
			'.asn'    => 'application/astound',
			'.asx'    => 'video/x-ms-asf',
			'.au'     => 'audio/basic',
			'.avi'    => 'video/x-msvideo',
			'.bat'    => 'text/plain',
			'.bcpio'  => 'application/x-bcpio',
			'.bin'    => 'application/octet-stream',
			'.bmp'    => 'image/x-ms-bmp',
			'.c'      => 'text/plain',
			'.cdf'    => 'application/x-cdf',
			'.class'  => 'application/x-java-applet',
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
			'.flv'    => 'video/x-flv',
			'.gif'    => 'image/gif',
			'.gtar'   => 'application/x-gtar',
			'.gz'     => 'application/gzip',
			'.h'      => 'text/plain',
			'.hdf'    => 'application/x-hdf',
			'.htm'    => 'text/html',
			'.html'   => 'text/html',
			'.ief'    => 'image/ief',
			'.iff'    => 'image/iff',
			'.jar'    => 'application/x-java-applet',
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
			'.mp4'    => 'video/mp4',
			'.mv4'    => 'video/mp4',
			'.ms'     => 'application/x-troff-ms',
			'.nc'     => 'application/x-netcdf',
			'.nws'    => 'message/rfc822',
			'.o'      => 'application/octet-stream',
			'.ogg'    => 'application/ogg',
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
			'.wax'    => 'audio/x-ms-wax',
			'.wiz'    => 'application/msword',
			'.wm'     => 'video/x-ms-wm',
			'.wma'    => 'audio/x-ms-wma',
			'.wmd'    => 'video/x-ms-wmd',
			'.wml'    => 'text/vnd.wap.wml',
			'.wmlc'   => 'application/vnd.wap.wmlc',
			'.wmls'   => 'text/vnd.wap.wmlscript',
			'.wmlsc'  => 'application/vnd.wap.wmlscriptc',
			'.wmv'    => 'video/x-ms-wmv',
			'.wmx'    => 'video/x-ms-wmx',
			'.wmz'    => 'video/x-ms-wmz',
			'.wvx'    => 'video/x-ms-wvx',
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
