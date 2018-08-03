<?php


class ilXlsFoParser
{
	/**
	 * @var ilSetting
	 */
	private $settings;

	/**
	 * @var ilPageFormats
	 */
	private $pageFormats;

	public function __construct(ilSetting $settings, ilPageFormats $pageFormats)
	{
		$this->settings = $settings;
		$this->pageFormats = $pageFormats;
	}

	/**
	 * @param array $formData
	 * @param string $backgroundImageName
	 * @return string
	 * @throws Exception
	 */
	public function parse($formData, $backgroundImageName)
	{
		$content = "<html><body>" . $formData['certificate_text'] . "</body></html>";
		$content = preg_replace("/<p>(&nbsp;){1,}<\\/p>/", "<p></p>", $content);
		$content = preg_replace("/<p>(\\s)*?<\\/p>/", "<p></p>", $content);
		$content = str_replace("<p></p>", "<p class=\"emptyrow\"></p>", $content);
		$content = str_replace("&nbsp;", "&#160;", $content);
		$content = preg_replace("//", "", $content);

		$check = new ilXMLChecker();
		$check->setXMLContent($content);
		$check->startParsing();

		if ($check->hasError()) {
			throw new Exception($this->lng->txt("certificate_not_well_formed"));
		}

		$xsl = file_get_contents("./Services/Certificate/xml/xhtml2fo.xsl");

		// additional font support
		$xsl = str_replace(
			'font-family="Helvetica, unifont"',
			'font-family="'. $this->settings->get('rpc_pdf_font','Helvetica, unifont') . '"',
			$xsl
		);

		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();

		if (strcmp($formData['pageformat'], 'custom') == 0) {
			$pageheight = $formData['pageheight'];
			$pagewidth = $formData['pagewidth'];
		}
		else {
			$pageformats = $this->pageFormats->fetchPageFormats();
			$pageheight = $pageformats[$formData['pageformat']]['height'];
			$pagewidth = $pageformats[$formData['pageformat']]['width'];
		}

		$params = array(
			'pageheight'      => $pageheight,
			'pagewidth'       => $pagewidth,
			'backgroundimage' => '[BACKGROUND_IMAGE]',
			'marginbody'      => implode(' ', array(
				$this->formatNumberString(ilUtil::stripSlashes($formData['margin_body']['top'])),
				$this->formatNumberString(ilUtil::stripSlashes($formData['margin_body']['right'])),
				$this->formatNumberString(ilUtil::stripSlashes($formData['margin_body']['bottom'])),
				$this->formatNumberString(ilUtil::stripSlashes($formData['margin_body']['left']))
			))
		);

		$output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", NULL, $args, $params);

		xslt_error($xh);
		xslt_free($xh);

		return $output;
	}

	/**
	 * @param string $backgroundImagePath
	 * @return mixed
	 */
	private function createConcreteBackgroundImagePath($backgroundImagePath = '')
	{
		return str_replace(
			array(CLIENT_WEB_DIR, '//'),
			array('[CLIENT_WEB_DIR]', '/'),
			$backgroundImagePath
		);
	}

	/**
	 * @param string $a_number
	 * @return float
	 */
	private function formatNumberString($a_number)
	{
		return str_replace(',', '.', $a_number);
	}

}
