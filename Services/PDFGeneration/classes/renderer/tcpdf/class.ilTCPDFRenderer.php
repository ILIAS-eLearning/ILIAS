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
	 * from ilRendererConfig
	 *
	 * @param \ilPropertyFormGUI $form
	 * @param string             $service
	 * @param string             $purpose
	 *
	 * @return \ilPropertyFormGUI|void
	 */
	public function addConfigElementsToForm(\ilPropertyFormGUI $form, $service, $purpose)
	{
		$margin_left = new ilTextInputGUI($this->lng->txt('margin_left'), 'margin_left');
		$form->addItem($margin_left);

		$margin_top = new ilTextInputGUI($this->lng->txt('margin_top'), 'margin_top');
		$form->addItem($margin_top);

		$margin_right = new ilTextInputGUI($this->lng->txt('margin_right'), 'margin_right');
		$form->addItem($margin_right);

		$margin_bottom = new ilTextInputGUI($this->lng->txt('margin_bottom'), 'margin_bottom');
		$form->addItem($margin_bottom);

		$image_scale = new ilTextInputGUI($this->lng->txt('image_scale'), 'image_scale');
		$form->addItem($image_scale);
	}

	/**
	 * from ilRendererConfig
	 *
	 * @param \ilPropertyFormGUI $form
	 * @param string             $service
	 * @param string             $purpose
	 * @param array              $config
	 *
	 * @return \ilPropertyFormGUI|void
	 */
	public function populateConfigElementsInForm(\ilPropertyFormGUI $form, $service, $purpose, $config)
	{
		$form->getItemByPostVar('margin_left')->setValue($config['margin_left']);
		$form->getItemByPostVar('margin_right')->setValue($config['margin_right']);
		$form->getItemByPostVar('margin_top')->setValue($config['margin_top']);
		$form->getItemByPostVar('margin_bottom')->setValue($config['margin_bottom']);
		$form->getItemByPostVar('image_scale')->setValue($config['image_scale']);
	}

	/**
	 * from ilRendererConfig
	 *
	 * @param \ilPropertyFormGUI $form
	 * @param string             $service
	 * @param string             $purpose
	 *
	 * @return bool
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
	 *
	 * @return array
	 */
	public function getConfigFromForm(\ilPropertyFormGUI $form, $service, $purpose)
	{
		$retval = array(
			'margin_left'		=> $form->getItemByPostVar('margin_left')->getValue(),
			'margin_right'		=> $form->getItemByPostVar('margin_right')->getValue(),
			'margin_top'		=> $form->getItemByPostVar('margin_top')->getValue(),
			'margin_bottom'		=> $form->getItemByPostVar('margin_bottom')->getValue(),
			'image_scale'		=> $form->getItemByPostVar('image_scale')->getValue(),
		);

		return $retval;
	}


	/**
	 * from ilRendererConfig
	 *
	 * @param string $service
	 * @param string $purpose
	 *
	 * @return array
	 */
	public function getDefaultConfig($service, $purpose)
	{
		$retval = array(
			'margin_left'		=> '10',
			'margin_top'		=> '10',
			'margin_right'		=> '10',
			'margin_bottom'		=> '10',
			'image_scale'		=> '1',
		);

		return $retval;
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

		$pdf->SetMargins($config['margin_left'], $config['margin_top'], $config['margin_right']);
		$pdf->SetAutoPageBreak('auto', $config['margin_buttom']);
		$pdf->setImageScale($config['image_scale']);

		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetFont('dejavusans', '', 10);
		$pdf->setSpacesRE('/[^\S\xa0]/'); // Fixing unicode/PCRE-mess #17547

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