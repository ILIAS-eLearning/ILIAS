<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePathFactoryTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var ilCertificatePathFactory
	 */
	private $certificatePathFactory;

	public function setUp()
	{
		$this->certificatePathFactory = new ilCertificatePathFactory();
	}

	public function testCreateTestCertificatePath()
	{
		$testObject = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$testObject->method('getType')->willReturn('tst');
		$testObject->method('getId')->willReturn(100);

		$certificatePath = $this->certificatePathFactory->createCertificatePath($testObject);

		$this->assertEquals('/assessment/certificates/100/', $certificatePath);
	}

	public function testCreateCourseCertificatePath()
	{
		$courseObject = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$courseObject->method('getType')->willReturn('crs');
		$courseObject->method('getId')->willReturn(100);

		$certificatePath = $this->certificatePathFactory->createCertificatePath($courseObject);

		$this->assertEquals('/course/certificates/100/', $certificatePath);
	}

	public function testCreateScormCertificatePath()
	{
		$scormObject = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$scormObject->method('getType')->willReturn('sahs');
		$scormObject->method('getId')->willReturn(100);

		$certificatePath = $this->certificatePathFactory->createCertificatePath($scormObject);

		$this->assertEquals('/scorm/certificates/100/', $certificatePath);
	}

	public function testCreateExerciseCertificatePath()
	{
		$excerciseObject = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$excerciseObject->method('getType')->willReturn('exc');
		$excerciseObject->method('getId')->willReturn(100);

		$certificatePath = $this->certificatePathFactory->createCertificatePath($excerciseObject);

		$this->assertEquals('/exercise/certificates/100/', $certificatePath);
	}

	/**
	 * @expectedException ilException
	 */
	public function testUnknownTypeThrowsException()
	{
		$unknownObject = $this->getMockBuilder('ilObject')
			->disableOriginalConstructor()
			->getMock();

		$unknownObject->method('getType')->willReturn('unknown');
		$unknownObject->method('getId')->willReturn(100);

		$certificatePath = $this->certificatePathFactory->createCertificatePath($unknownObject);
	}
}
