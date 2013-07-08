<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'class.ilPDFGenerationJob.php';

/**
 * Class ilPDFGeneration
 * 
 * Dispatcher to route PDF-Generation jobs to the appropriate handling mechanism.
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 * 
 */
class ilPDFGeneration 
{
	public static function doJob(ilPDFGenerationJob $job)
	{
		/*
		 * This place currently supports online the TCPDF-Generator. In future versions/iterations, this place
		 * may serve to initialize other mechanisms and route jobs to them.
		 */
		require_once 'class.ilTCPDFGenerator.php';
		ilTCPDFGenerator::generatePDF($job);
	}
}