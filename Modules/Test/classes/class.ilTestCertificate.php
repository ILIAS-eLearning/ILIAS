<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* Export test results as PDF certificates
*
* This class defines everything to export test results as PDF
* certificates using XML-FO techniques
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTest
*/
class ilTestCertificate
{
	/**
	* Question id
	*
	* A unique question id
	*
	* @var integer
	*/
	var $object;
	/**
	* The reference to the ILIAS control class
	*
	* The reference to the ILIAS control class
	*
	* @var ctrl
	*/
	var $ctrl;
	/**
	* The reference to the ILIAS tree class
	*
	* The reference to the ILIAS tree class
	*
	* @var ctrl
	*/
	var $tree;
	/**
	* The reference to the ILIAS class
	*
	* The reference to the ILIAS class
	*
	* @var object
	*/
	var $ilias;

	/**
	* The reference to the Template class
	*
	* The reference to the Template class
	*
	* @var object
	*/
	var $tpl;

	/**
	* The reference to the Language class
	*
	* The reference to the Language class
	*
	* @var object
	*/
	var $lng;

	/**
	* ilTestCertificate constructor
	*
	* The constructor takes possible arguments an creates an instance of 
	* the ilTestCertificate object.
	*
	* @param object $a_object A reference to the test container object
	* @access public
	*/
	function ilTestCertificate(&$a_object)
	{
		global $lng, $tpl, $ilCtrl, $ilias, $tree;

    $this->lng =& $lng;
    $this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ilias =& $ilias;
		$this->object =& $a_object;
		$this->tree =& $tree;
	}
	
	/**
	* Returns the filesystem path of the certificate
	*
	* Returns the filesystem path of the certificate
	*
	* @return string The filesystem path of the certificate
	* @access public
	*/
	function getCertificatePath()
	{
		return CLIENT_WEB_DIR . "/assessment/certificates/" . $this->object->getId() . "/";
	}
	
	/**
	* Returns the filesystem path of the background image
	*
	* Returns the filesystem path of the background image
	*
	* @return string The filesystem path of the background image
	* @access public
	*/
	function getBackgroundImagePath()
	{
		return CLIENT_WEB_DIR . "/assessment/certificates/" . $this->object->getId() . "/" . $this->getBackgroundImageName();
	}

	/**
	* Returns the filename of the background image
	*
	* Returns the filename of the background image
	*
	* @return string The filename of the background image
	* @access public
	*/
	function getBackgroundImageName()
	{
		return "background.jpg";
	}

	/**
	* Returns the filesystem path of the background image thumbnail
	*
	* Returns the filesystem path of the background image thumbnail
	*
	* @return string The filesystem path of the background image thumbnail
	* @access public
	*/
	function getBackgroundImageThumbPath()
	{
		return CLIENT_WEB_DIR . "/assessment/certificates/" . $this->object->getId() . "/" . $this->getBackgroundImageName() . ".thumb.jpg";
	}

	/**
	* Returns the filesystem path of the background image temp file during upload
	*
	* Returns the filesystem path of the background image temp file during upload
	*
	* @return string The filesystem path of the background image temp file
	* @access public
	*/
	function getBackgroundImageTempfilePath()
	{
		return CLIENT_WEB_DIR . "/assessment/certificates/" . $this->object->getId() . "/background_upload";
	}

	/**
	* Returns the filesystem path of the XSL-FO file
	*
	* Returns the filesystem path of the XSL-FO file
	*
	* @return string The filesystem path of the XSL-FO file
	* @access public
	*/
	function getXSLPath()
	{
		return CLIENT_WEB_DIR . "/assessment/certificates/" . $this->object->getId() . "/" . $this->getXSLName();
	}
	
	/**
	* Returns the filename of the XSL-FO file
	*
	* Returns the filename of the XSL-FO file
	*
	* @return string The filename of the XSL-FO file
	* @access public
	*/
	function getXSLName()
	{
		return "certificate.xml";
	}
	
	/**
	* Returns the web path of the background image
	*
	* Returns the web path of the background image
	*
	* @return string The web path of the background image
	* @access public
	*/
	function getBackgroundImagePathWeb()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/certificates/" . $this->object->getId() . "/" . $this->getBackgroundImageName();
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
	}
	
	/**
	* Deletes the background image of a certificate
	*
	* Deletes the background image of a certificate
	*
	* @return boolean TRUE if the process succeeds
	* @access public
	*/
	function deleteBackgroundImage()
	{
		global $ilLog;
		$result = TRUE;
		if (file_exists($this->getBackgroundImageThumbPath()))
		{
			$ilLog->write("delete " . $this->getBackgroundImageThumbPath());
			$result = $result & unlink($this->getBackgroundImageThumbPath());
		}
		if (file_exists($this->getBackgroundImagePath()))
		{
			$ilLog->write("delete " . $this->getBackgroundImagePath());
			$result = $result & unlink($this->getBackgroundImagePath());
		}
		if (file_exists($this->getBackgroundImageTempfilePath()))
		{
			$ilLog->write("delete " . $this->getBackgroundImageTempfilePath());
			$result = $result & unlink($this->getBackgroundImageTempfilePath());
		}
		return $result;
	}

	/**
	* Deletes the certificate and all it's data
	*
	* Deletes the certificate and all it's data
	*
	* @access public
	*/
	function deleteCertificate()
	{
		global $ilLog;
		if (file_exists($this->getCertificatePath()))
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::delDir($this->getCertificatePath());
		}
	}

/**
* Checks the certificate fields for errors prior to saving the certificate
*
* Checks the certificate fields for errors prior to saving the certificate
*
* @param array $form_fields An associative array containing the form fields of the certificate editor
* @return TRUE if the check succeeded, an error message otherwise
* @access public
*/
	function checkCertificateInput($form_fields)
	{
		// 1. check the required fields
		if ((strlen($form_fields["padding_top"]) == 0) ||
			(strlen($form_fields["margin_body_top"]) == 0) ||
			(strlen($form_fields["margin_body_right"]) == 0) ||
			(strlen($form_fields["margin_body_bottom"]) == 0) ||
			(strlen($form_fields["margin_body_left"]) == 0) ||
			(strlen($form_fields["certificate_text"]) == 0))
		{
			return $this->lng->txt("fill_out_all_required_fields");
		}
		
		$unitexpression = "^([\d\.]+)(pt|pc|px|em|mm|cm|in){0,1}\$";
		if (!preg_match("/$unitexpression/", $form_fields["padding_top"], $matches))
		{
			return $this->lng->txt("certificate_wrong_unit");
		}
		if (strcmp($form_fields["pageformat"], "custom") == 0)
		{
			if (!preg_match("/$unitexpression/", $form_fields["pageheight"], $matches))
			{
				return $this->lng->txt("certificate_wrong_unit");
			}
			if (!preg_match("/$unitexpression/", $form_fields["pagewidth"], $matches))
			{
				return $this->lng->txt("certificate_wrong_unit");
			}
		}
		if (!preg_match("/$unitexpression/", $form_fields["margin_body_top"], $matches))
		{
			return $this->lng->txt("certificate_wrong_unit");
		}
		if (!preg_match("/$unitexpression/", $form_fields["margin_body_right"], $matches))
		{
			return $this->lng->txt("certificate_wrong_unit");
		}
		if (!preg_match("/$unitexpression/", $form_fields["margin_body_bottom"], $matches))
		{
			return $this->lng->txt("certificate_wrong_unit");
		}
		if (!preg_match("/$unitexpression/", $form_fields["margin_body_left"], $matches))
		{
			return $this->lng->txt("certificate_wrong_unit");
		}
		if (strlen($form_fields["certificate_text"]) == 0)
		{
			return $this->lng->txt("certificate_missing_text");
		}
		if (strlen($form_fields["certificate_text"]) > 0)
		{
			include_once "class.ilXMLChecker.php";
			$check = new ilXMLChecker();
			$check->setXMLContent(str_replace("&nbsp;", " ", "<html>" . $form_fields["certificate_text"] . "</html>"));
			$check->startParsing();
			if ($check->hasError())
			{
				return $this->lng->txt("certificate_not_well_formed");
			}
		}
		return TRUE;
	}
	
	/**
	* Convert the XSL-FO to the certificate text and the form settings using XSL transformation
	*
	* Convert the XSL-FO to the certificate text and the form settings using XSL transformation
	*
	* @access private
	*/
	function processFO2XHTML()
	{
		if (file_exists($this->getXSLPath()))
		{
			$xslfo = file_get_contents($this->getXSLPath());
		}
		// retrieve form information (using a dirty way with regular expressions)
		$pagewidth = "21cm";
		if (preg_match("/page-width\=\"([^\"]+)\"/", $xslfo, $matches))
		{
			$pagewidth = $matches[1];
		}
		$pageheight = "29.7cm";
		if (preg_match("/page-height\=\"([^\"]+)\"/", $xslfo, $matches))
		{
			$pageheight = $matches[1];
		}
		$pagesize = "custom";
		if (((strcmp($pageheight, "29.7cm") == 0) || (strcmp($pageheight, "297mm") == 0)) && ((strcmp($pagewidth, "21cm") == 0) || (strcmp($pagewidth, "210mm") == 0)))
		{
			$pagesize = "a4";
		}
		else if (((strcmp($pagewidth, "29.7cm") == 0) || (strcmp($pagewidth, "297mm") == 0)) && ((strcmp($pageheight, "21cm") == 0) || (strcmp($pageheight, "210mm") == 0)))
		{
			$pagesize = "a4landscape";
		}
		else if (((strcmp($pageheight, "21cm") == 0) || (strcmp($pageheight, "210mm") == 0)) && ((strcmp($pagewidth, "14.8cm") == 0) || (strcmp($pagewidth, "148mm") == 0)))
		{
			$pagesize = "a5";
		}
		else if (((strcmp($pagewidth, "21cm") == 0) || (strcmp($pagewidth, "210mm") == 0)) && ((strcmp($pageheight, "14.8cm") == 0) || (strcmp($pageheight, "148mm") == 0)))
		{
			$pagesize = "a5landscape";
		}
		else if (((strcmp($pageheight, "11in") == 0)) && ((strcmp($pagewidth, "8.5in") == 0)))
		{
			$pagesize = "letter";
		}
		else if (((strcmp($pagewidth, "11in") == 0)) && ((strcmp($pageheight, "8.5in") == 0)))
		{
			$pagesize = "letterlandscape";
		}
		
		/* only needed for upload of xsl-fo to convert existing background image 
		$backgroundimage = "";
		if (preg_match("/background-image\=\"url\([']{0,1}([^\)]+)[']{0,1}\)/", $xslfo, $matches))
		{
			$backgroundimage = $matches[1];
			echo $backgroundimage;
		}
		*/
		$paddingtop = "0cm";
		if (preg_match("/padding-top\=\"([^\"]+)\"/", $xslfo, $matches))
		{
			$paddingtop = $matches[1];
		}
		$marginbody_top = "0cm";
		$marginbody_right = "2cm";
		$marginbody_bottom = "0cm";
		$marginbody_left = "2cm";
		if (preg_match("/fo:flow[^>]*margin\=\"([^\"]+)\"/", $xslfo, $matches))
		{
			$marginbody = $matches[1];
			if (preg_match_all("/([^\s]+)/", $marginbody, $matches))
			{
				$marginbody_top = $matches[1][0];
				$marginbody_right = $matches[1][1];
				$marginbody_bottom = $matches[1][2];
				$marginbody_left = $matches[1][3];
			}
		}

		$xsl = file_get_contents("./Modules/Test/xml/fo2xhtml.xsl");
		if ((strlen($xslfo)) && (strlen($xsl)))
		{
			$args = array( '/_xml' => $xslfo, '/_xsl' => $xsl );
			$xh = xslt_create();
			$output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", NULL, $args, NULL);
			xslt_error($xh);
			xslt_free($xh);
		}

		$output = preg_replace("/<\?xml[^>]+?>/", "", $output);
		
		return array(
			"pageformat" => $pagesize,
			"pagewidth" => $pagewidth,
			"pageheight" => $pageheight,
			"padding_top" => $paddingtop,
			"margin_body_top" => $marginbody_top,
			"margin_body_right" => $marginbody_right,
			"margin_body_bottom" => $marginbody_bottom,
			"margin_body_left" => $marginbody_left,
			"certificate_text" => $output,
			"certificate_visibility" => $this->object->getCertificateVisibility()
		);
	}
	
	/**
	* Convert the certificate text to XSL-FO using XSL transformation
	*
	* Convert the certificate text to XSL-FO using XSL transformation
	*
	* @param array $form_data The form data
	* @return string XSL-FO code
	* @access private
	*/
	function processXHTML2FO($form_data, $for_export = FALSE)
	{
		$content = "<html><body>".$form_data["certificate_text"]."</body></html>";
		$content = str_replace("<p>&nbsp;</p>", "<p><br /></p>", $content);
		$content = str_replace("&nbsp;", " ", $content);
		$xsl = file_get_contents("./Modules/Test/xml/xhtml2fo.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
		if (strcmp($form_data["pageformat"], "custom") == 0)
		{
			$pageheight = $form_data["pageheight"];
			$pagewidth = $form_data["pagewidth"];
		}
		else
		{
			$pageformats = $this->getPageFormats();
			$pageheight = $pageformats[$form_data["pageformat"]]["height"];
			$pagewidth = $pageformats[$form_data["pageformat"]]["width"];
		}
		if ($for_export)
		{
			$backgroundimage = $this->hasBackgroundImage() ? $this->getBackgroundImageName() : "";
		}
		else
		{
			$backgroundimage = $this->hasBackgroundImage() ? $this->getBackgroundImagePath()  . $this->getDummyParameter() : "";
		}
		$params = array(
			"pageheight" => $pageheight, 
			"pagewidth" => $pagewidth,
			"backgroundimage" => $backgroundimage,
			"marginbody" => $form_data["margin_body_top"] . " " . $form_data["margin_body_right"] . " " . $form_data["margin_body_bottom"] . " " . $form_data["margin_body_left"],
			"paddingtop" => $form_data["padding_top"]
		);
		$output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", NULL, $args, $params);
		xslt_error($xh);
		xslt_free($xh);
		return $output;
	}

	/**
	* Saves the visibility settings of the certificate
	*
	* Saves the visibility settings of the certificate
	*
	* @param integer $a_value The value for the visibility settings (0 = always, 1 = only passed,  2 = never)
	* @access private
	*/
	function saveCertificateVisibility($a_value)
	{
		$this->object->saveCertificateVisibility($a_value);
	}
	
	/**
	* Exchanges the variables in the certificate text with given values
	*
	* Exchanges the variables in the certificate text with given values
	*
	* @param string $certificate_text The XSL-FO certificate text
	* @param array $user_data An associative array containing the variables and the values to replace
	* @return string XSL-FO code
	* @access private
	*/
	function exchangeCertificateVariables($certificate_text, $user_data = array())
	{
		if (count($user_data) == 0)
		{
			include_once "./classes/class.ilFormat.php";
			$user_data = array(
				"[USER_FULLNAME]" => $this->lng->txt("certificate_var_user_fullname"),
				"[USER_FIRSTNAME]" => $this->lng->txt("certificate_var_user_firstname"),
				"[USER_LASTNAME]" => $this->lng->txt("certificate_var_user_lastname"),
				"[RESULT_PASSED]" => $this->lng->txt("certificate_var_result_passed"),
				"[RESULT_POINTS]" => $this->lng->txt("certificate_var_result_points"),
				"[RESULT_PERCENT]" => $this->lng->txt("certificate_var_result_percent"),
				"[MAX_POINTS]" => $this->lng->txt("certificate_var_max_points"),
				"[RESULT_MARK_SHORT]" => $this->lng->txt("certificate_var_result_mark_short"),
				"[RESULT_MARK_LONG]" => $this->lng->txt("certificate_var_result_mark_long"),
				"[TEST_TITLE]" => $this->object->getTitle(),
				"[DATE]" => ilFormat::formatDate(ilFormat::unixtimestamp2datetime(time()), "date"),
				"[DATETIME]" => ilFormat::formatDate(ilFormat::unixtimestamp2datetime(time()), "datetime", TRUE)
			);
		}
		foreach ($user_data as $var => $value)
		{
			$certificate_text = str_replace($var, $value, $certificate_text);
		}
		return $certificate_text;
	}
	
	function createArchiveDirectory()
	{
		$dir = $this->getCertificatePath() . time() . "__" . IL_INST_ID . "__" . "test" . "__" . $this->object->getId() . "__certificate/";
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::makeDirParents($dir);
		return $dir;
	}
	
	function addPDFtoArchiveDirectory($pdfdata, $dir, $filename)
	{
		$fh = fopen($dir . $filename, "wb");
		fwrite($fh, $pdfdata);
		fclose($fh);
	}
	
	/**
	* Creates a ZIP file with user certificates
	*
	* Creates a ZIP file with user certificates
	*
	* @access private
	*/
	function outCertificates($userfilter = "", $passedonly = FALSE)
	{
		global $ilUser;
		
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$archive_dir = $this->createArchiveDirectory();
		$total_users = array();
		$total_users =& $this->object->evalTotalPersonsArray();
		if (count($total_users))
		{
			foreach ($total_users as $active_id => $name)
			{
				$user_id = $this->object->_getUserIdFromActiveId($active_id);
				$pdf = $this->outCertificate($active_id, "", FALSE, $userfilter, $passedonly);
				if (strlen($pdf))
				{
					$this->addPDFtoArchiveDirectory($pdf, $archive_dir, $user_id . "_" . str_replace(" ", "_", ilUtil::getASCIIFilename($name)) . ".pdf");
				}
			}
			$zipfile = time() . "__" . IL_INST_ID . "__" . "test" . "__" . $this->object->getId() . "__certificates.zip";
			ilUtil::zip($archive_dir, $this->getCertificatePath() . $zipfile);
			ilUtil::delDir($archive_dir);
			ilUtil::deliverFile($this->getCertificatePath() . $zipfile, $zipfile, "application/zip");
		}
	}

	/**
	* Creates a PDF preview of the XSL-FO certificate
	*
	* Creates a PDF preview of the XSL-FO certificate and delivers it
	*
	* @access private
	*/
	function outCertificate($active_id, $pass, $deliver = TRUE, $userfilter = "", $passedonly = FALSE)
	{
		if (strlen($pass))
		{
			$result_array =& $this->object->getTestResult($active_id, $pass);
		}
		else
		{
			$result_array =& $this->object->getTestResult($active_id);
		}
		if (($passedonly) && ($result_array["test"]["passed"] == FALSE)) return "";
		$passed = $result_array["test"]["passed"] ? $this->lng->txt("certificate_passed") : $this->lng->txt("certificate_failed");
		if (!$result_array["test"]["total_max_points"])
		{
			$percentage = 0;
		}
		else
		{
			$percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
		}
		$mark_obj = $this->object->mark_schema->getMatchingMark($percentage);
		$user_id = $this->object->_getUserIdFromActiveId($active_id);
		include_once './Services/User/classes/class.ilObjUser.php';
		$user_data = ilObjUser::_lookupName($user_id);
		if (strlen($userfilter))
		{
			if (!@preg_match("/$userfilter/i", $user_data["lastname"] . ", " . $user_data["firstname"] . " " . $user_data["title"]))
			{
				return "";
			}
		}
		include_once "./classes/class.ilFormat.php";
		$user_data = array(
			"[USER_FULLNAME]" => trim($user_data["title"] . " " . $user_data["firstname"] . " " . $user_data["lastname"]),
			"[USER_FIRSTNAME]" => $user_data["firstname"],
			"[USER_LASTNAME]" => $user_data["lastname"],
			"[RESULT_PASSED]" => $passed,
			"[RESULT_POINTS]" => $result_array["test"]["total_reached_points"],
			"[RESULT_PERCENT]" => sprintf("%2.2f", $percentage) . "%",
			"[MAX_POINTS]" => $result_array["test"]["total_max_points"],
			"[RESULT_MARK_SHORT]" => $mark_obj->getShortName(),
			"[RESULT_MARK_LONG]" => $mark_obj->getOfficialName(),
			"[TEST_TITLE]" => $this->object->getTitle(),
			"[DATE]" => ilFormat::formatDate(ilFormat::unixtimestamp2datetime(time()), "date"),
			"[DATETIME]" => ilFormat::formatDate(ilFormat::unixtimestamp2datetime(time()), "datetime", TRUE)
		);
		$xslfo = file_get_contents($this->getXSLPath());
		include_once "./Services/Transformation/classes/class.ilFO2PDF.php";
		$fo2pdf = new ilFO2PDF();
		$fo2pdf->setFOString($this->exchangeCertificateVariables($xslfo, $user_data));
		$result = $fo2pdf->send();
		if ($deliver)
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::deliverData($result, "certificate.pdf", "application/pdf");
		}
		else
		{
			return $result;
		}
	}

	/**
	* Creates a PDF preview of the XSL-FO certificate
	*
	* Creates a PDF preview of the XSL-FO certificate and delivers it
	*
	* @access private
	*/
	function createPreview()
	{
		$xslfo = file_get_contents($this->getXSLPath());
		include_once "./Services/Transformation/classes/class.ilFO2PDF.php";
		$fo2pdf = new ilFO2PDF();
		$fo2pdf->setFOString($this->exchangeCertificateVariables($xslfo));
		$result = $fo2pdf->send();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::deliverData($result, "certificate.pdf", "application/pdf");
	}
	
	/**
	* Saves the XSL-FO code to the ILIAS web directory
	*
	* Saves the XSL-FO code to the ILIAS web directory
	*
	* @param string $xslfo XSL-FO code
	* @access private
	*/
	function saveCertificate($xslfo, $filename = "")
	{
		if (!file_exists($this->getCertificatePath()))
		{
			ilUtil::makeDirParents($this->getCertificatePath());
		}
		if (strlen($filename) == 0)
		{
			$filename = $this->getXSLPath();
		}
		$fh = fopen($filename, "w");
		fwrite($fh, $xslfo);
		fclose($fh);
	}
	
	/**
	* Uploads a background image for the certificate
	*
	* Uploads a background image for the certificate. Creates a new directory for the
	* certificate if needed. Removes an existing certificate image if necessary
	*
	* @param string $image_tempfilename Name of the temporary uploaded image file
	* @return integer An errorcode if the image upload fails, 0 otherwise
	* @access public
	*/
	function uploadBackgroundImage($image_tempfilename)
	{
		if (!empty($image_tempfilename))
		{
			$image_filename = "background_upload";
			$convert_filename = $this->getBackgroundImageName();
			$imagepath = $this->getCertificatePath();
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			// upload the file
			if (!ilUtil::moveUploadedFile($image_tempfilename, $image_filename, $this->getBackgroundImageTempfilePath()))
			{
				return FALSE;
			}
			// convert the uploaded file to JPEG
			ilUtil::convertImage($this->getBackgroundImageTempfilePath(), $this->getBackgroundImagePath(), "JPEG");
			ilUtil::convertImage($this->getBackgroundImageTempfilePath(), $this->getBackgroundImageThumbPath(), "JPEG", 100);
			if (!file_exists($this->getBackgroundImagePath()))
			{
				// something went wrong converting the file. use the original file and hope, that PDF can work with it
				if (!ilUtil::moveUploadedFile($this->getBackgroundImageTempfilePath(), $convert_filename, $this->getBackgroundImagePath()))
				{
					return FALSE;
				}
			}
			unlink($this->getBackgroundImageTempfilePath());
			if (file_exists($this->getBackgroundImagePath()) && (filesize($this->getBackgroundImagePath()) > 0))
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	* Checks for the background image of the certificate
	*
	* Checks for the background image of the certificate
	*
	* @return boolean Returns TRUE if the certificate has a background image, FALSE otherwise
	* @access private
	*/
	function hasBackgroundImage()
	{
		if (file_exists($this->getBackgroundImagePath()) && (filesize($this->getBackgroundImagePath()) > 0))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	* Checks the status of the certificate
	*
	* Checks the status of the certificate
	*
	* @return boolean Returns TRUE if the certificate is complete, FALSE otherwise
	* @access private
	*/
	function isComplete()
	{
		if (file_exists($this->getCertificatePath()))
		{
			if (file_exists($this->getXSLPath()) && (filesize($this->getXSLPath()) > 0))
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	* Checks the status of the certificate
	*
	* Checks the status of the certificate
	*
	* @return boolean Returns TRUE if the certificate is complete, FALSE otherwise
	* @access private
	*/
	function _isComplete($obj_id)
	{
		$certificatepath = CLIENT_WEB_DIR . "/assessment/certificates/" . $obj_id . "/";
		if (file_exists($certificatepath))
		{
			$xslpath = CLIENT_WEB_DIR . "/assessment/certificates/" . $obj_id . "/" . ilTestCertificate::getXSLName();
			if (file_exists($xslpath) && (filesize($xslpath) > 0))
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	* Retrieves predefined page formats
	*
	* Retrieves predefined page formats
	*
	* @return array Associative array containing page formats
	* @access private
	*/
	function getPageFormats()
	{
		return array(
			"a4" => array(
				"name" => $this->lng->txt("certificate_a4"), // (297 mm x 210 mm)
				"value" => "a4",
				"width" => "210mm",
				"height" => "297mm"
			),
			"a4landscape" => array(
				"name" => $this->lng->txt("certificate_a4_landscape"), // (210 mm x 297 mm)",
				"value" => "a4landscape",
				"width" => "297mm",
				"height" => "210mm"
			),
			"a5" => array(
				"name" => $this->lng->txt("certificate_a5"), // (210 mm x 148.5 mm)
				"value" => "a5",
				"width" => "148mm",
				"height" => "210mm"
			),
			"a5landscape" => array(
				"name" => $this->lng->txt("certificate_a5_landscape"), // (148.5 mm x 210 mm)
				"value" => "a5landscape",
				"width" => "210mm",
				"height" => "148mm"
			),
			"letter" => array(
				"name" => $this->lng->txt("certificate_letter"), // (11 inch x 8.5 inch)
				"value" => "letter",
				"width" => "8.5in",
				"height" => "11in"
			),
			"letterlandscape" => array(
				"name" => $this->lng->txt("certificate_letter_landscape"), // (11 inch x 8.5 inch)
				"value" => "letterlandscape",
				"width" => "8.5in",
				"height" => "11in"
			),
			"custom" => array(
				"name" => $this->lng->txt("certificate_custom"),
				"value" => "custom",
				"width" => "",
				"height" => ""
			)
		);
	}

	/**
	* Builds an export file in ZIP format and delivers it
	*
	* Builds an export file in ZIP format and delivers it
	*
	* @access private
	*/
	function deliverExportFileXML()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$exportpath = $this->createArchiveDirectory();
		ilUtil::makeDir($exportpath);
		$xsl = file_get_contents($this->getXSLPath());
		$xslexport = str_replace($this->getCertificatePath(), "", $xsl);
		// save export xsl file
		$this->saveCertificate($xslexport, $exportpath . $this->getXSLName());
		// save background image
		if ($this->hasBackgroundImage())
		{
			copy($this->getBackgroundImagePath(), $exportpath . $this->getBackgroundImageName());
		}
		$zipfile = time() . "__" . IL_INST_ID . "__" . "test" . "__" . $this->object->getId() . "__certificate.zip";
		ilUtil::zip($exportpath, $this->getCertificatePath() . $zipfile);
		ilUtil::delDir($exportpath);
		ilUtil::deliverFile($this->getCertificatePath() . $zipfile, $zipfile, "application/zip");
	}
	
	/**
	* Reads an import ZIP file and creates a certificate of it
	*
	* Reads an import ZIP file and creates a certificate of it
	*
	* @return boolean TRUE if the import succeeds, FALSE otherwise
	* @access private
	*/
	function importCertificate($zipfile, $filename)
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$importpath = $this->createArchiveDirectory();
		if (!ilUtil::moveUploadedFile($zipfile, $filename, $importpath . $filename))
		{
			ilUtil::delDir($importpath);
			return FALSE;
		}
		ilUtil::unzip($importpath . $filename, TRUE);
		$subdir = str_replace(".zip", "", strtolower($filename)) . "/";
		$copydir = "";
		if (is_dir($importpath . $subdir))
		{
			$dirinfo = ilUtil::getDir($importpath . $subdir);
			$copydir = $importpath . $subdir;
		}
		else
		{
			$dirinfo = ilUtil::getDir($importpath);
			$copydir = $importpath;
		}
		$xmlfiles = 0;
		$otherfiles = 0;
		foreach ($dirinfo as $file)
		{
			if (strcmp($file["type"], "file") == 0)
			{
				if (strpos($file["entry"], ".xml") !== FALSE) 
				{
					$xmlfiles++;
				}
				else if (strpos($file["entry"], ".zip") !== FALSE)
				{
				}
				else
				{
					$otherfiles++;
				}
			}
		}
		// if one XML file is in the archive, we try to import it
		if ($xmlfiles == 1)
		{
			foreach ($dirinfo as $file)
			{
				if (strcmp($file["type"], "file") == 0)
				{
					if (strpos($file["entry"], ".xml") !== FALSE) 
					{
						$xsl = file_get_contents($copydir . $file["entry"]);
						// as long as we cannot make RPC calls in a given directory, we have
						// to add the complete path to every url
						$xsl = preg_replace("/url\([']{0,1}(.*?)[']{0,1}\)/", "url(" . $this->getCertificatePath() . "\${1})", $xsl);
						$this->saveCertificate($xsl);
					}
					else if (strpos($file["entry"], ".zip") !== FALSE)
					{
					}
					else
					{
						@copy($copydir . $file["entry"], $this->getCertificatePath() . $file["entry"]);
						if (strcmp($this->getBackgroundImagePath(), $this->getCertificatePath() . $file["entry"]) == 0)
						{
							// upload of the background image, create a preview
							ilUtil::convertImage($this->getBackgroundImagePath(), $this->getBackgroundImageThumbPath(), "JPEG", 100);
						}
					}
				}
			}
		}
		else
		{
			ilUtil::delDir($importpath);
			return FALSE;
		}
		ilUtil::delDir($importpath);
		return TRUE;
	}

	/**
	* Creates a dummy parameter to add to an image url to prevent caching
	*
	* Creates a dummy parameter to add to an image url to prevent caching
	*
	* @return string The dummy parameter
	* @access private
	*/
	function getDummyParameter()
	{
		return "?dummy=" . time();
	}
}

?>
