<?php

require_once './Services/PDFGeneration/interfaces/interface.ilRendererConfig.php';
require_once './Services/PDFGeneration/interfaces/interface.ilPDFRenderer.php';

class ilTCPDFRenderer implements ilRendererConfig, ilPDFRenderer
{
	/** @var ilLanguage $lng */
	protected $lng;

	/**
	 * from ilPlugin
	 *
	 * ilDummyRendererPlugin constructor.
	 */
	public function __construct()
	{
		global $DIC;
		$this->lng = $DIC['lng'];
	}

	/**
	 * from ilPlugin
	 *
	 * @return string
	 */
	public function getPluginName()
	{
		return $this->lng->txt('pdfgen_renderer_dummyrender_plugname');
	}

	/**
	 * from ilRendererConfig
	 *
	 * @param \ilPropertyFormGUI $form
	 * @param string             $service
	 * @param string             $purpose
	 */
	public function addConfigElementsToForm(\ilPropertyFormGUI $form, $service, $purpose)
	{
		$input = new ilTextInputGUI($this->lng->txt('number'), 'number');
		$form->addItem($input);
	}

	/**
	 * from ilRendererConfig
	 *
	 * @param \ilPropertyFormGUI $form
	 * @param string             $service
	 * @param string             $purpose
	 * @param array              $config
	 */
	public function populateConfigElementsInForm(\ilPropertyFormGUI $form, $service, $purpose, $config)
	{
		$form->getItemByPostVar('number')->setValue($config['number']);
	}

	/**
	 * from ilRendererConfig
	 *
	 * @param \ilPropertyFormGUI $form
	 * @param string             $service
	 * @param string             $purpose
	 */
	public function validateConfigInForm(\ilPropertyFormGUI $form, $service, $purpose)
	{
		if(true)
		{
			return true;
		}
	}

	/**
	 * from ilRendererConfig
	 *
	 * @param \ilPropertyFormGUI $form
	 * @param string             $service
	 * @param string             $purpose
	 */
	public function getConfigFromForm(\ilPropertyFormGUI $form, $service, $purpose)
	{
		return array('number' => $form->getItemByPostVar('number')->getValue());
	}


	/**
	 * from ilRendererConfig
	 *
	 * @param string $service
	 * @param string $purpose
	 */
	public function getDefaultConfig($service, $purpose)
	{
		return array('number' => 42);
	}

	/**
	 * from ilPDFRenderer
	 *
	 * @param string              $service
	 * @param string              $purpose
	 * @param array               $config
	 * @param \ilPDFGenerationJob $job
	 */
	public function generatePDF($service, $purpose, $config, $job)
	{
		require_once 'libs/composer/vendor/autoload.php';

		// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator( $job->getCreator() );
		$pdf->SetAuthor( $job->getAuthor() );
		$pdf->SetTitle( $job->getTitle() );
		$pdf->SetSubject( $job->getSubject() );
		$pdf->SetKeywords( $job->getKeywords() );

		//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING); // TODO
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN)); // TODO
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA)); // TODO
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED); // TODO
		$pdf->SetMargins($job->getMarginLeft(), $job->getMarginTop(), $job->getMarginRight());
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER); // TODO
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER); // TODO
		$pdf->SetAutoPageBreak($job->getAutoPageBreak(), $job->getMarginBottom());
		$pdf->setImageScale($job->getImageScale());
		$pdf->SetFont('dejavusans', '', 10); // TODO

		$pdf->setSpacesRE('/[^\S\xa0]/'); // Fixing unicode/PCRE-mess #17547

		/* // TODO
		// set some language-dependent strings (optional)
		if (file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
		*/
		// set font

		foreach ($job->getPages() as $page)
		{
			$page = ' '.$page;
			$pdf->AddPage();
			$pdf->writeHTML($page, true, false, true, false, '');
		}
		$result = $pdf->Output($job->getFilename(), $job->getOutputMode() ); // (I - Inline, D - Download, F - File)

		if(in_array($job->getOutputMode(), array('I', 'D')))
		{
			exit();
		}
	}
}