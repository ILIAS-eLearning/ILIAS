<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfActionTest extends \PHPUnit_Framework_TestCase
{
	public function testCreatePdfWillCreatedAndIsDownloadable()
	{
		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$pdfGenerator = $this->getMockBuilder('ilPdfGenerator')
			->disableOriginalConstructor()
			->setMethods(array('generateCurrentActiveCertificate'))
			->getMock();

		$pdfGenerator->method('generateCurrentActiveCertificate')
			->willReturn('Something');

		$ilUtilHelper = $this->getMockBuilder('ilUtilHelper')
			->getMock();

		$pdfAction = new ilCertificatePdfAction($logger, $pdfGenerator, $ilUtilHelper);

		$result = $pdfAction->createPDF(10, 200);

		$this->assertEquals('Something', $result);
	}

	public function testPdfDownloadAction()
	{
		$logger = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();

		$pdfGenerator = $this->getMockBuilder('ilPdfGenerator')
			->disableOriginalConstructor()
			->setMethods(array('generateCurrentActiveCertificate'))
			->getMock();

		$pdfGenerator->method('generateCurrentActiveCertificate')
			->willReturn('Something');

		$ilUtilHelper = $this->getMockBuilder('ilUtilHelper')
			->getMock();

		$ilUtilHelper->method('deliverData')
			->with(
				'Something',
				'Certificate.pdf',
				'application/pdf'
			);

		$pdfAction = new ilCertificatePdfAction($logger, $pdfGenerator, $ilUtilHelper);

		$result = $pdfAction->downloadPdf(10, 200);

		$this->assertEquals('Something', $result);
	}
}
