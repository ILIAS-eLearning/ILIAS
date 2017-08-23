<?php

interface ilPDFRenderer
{
	/**
	 * @param string              $service
	 * @param string              $purpose
	 * @param array               $config
	 * @param \ilPDFGenerationJob $job
	 *
	 * @return void
	 */
	public function generatePDF($service, $purpose, $config, $job);
}