<?php

require_once './Services/PDFGeneration/interfaces/interface.ilRendererConfig.php';
require_once './Services/PDFGeneration/interfaces/interface.ilPDFRenderer.php';

class ilPhantomJSRenderer implements ilRendererConfig, ilPDFRenderer
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
		return "Works.";
	}
}