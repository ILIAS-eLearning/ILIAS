<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/encoders/class.ilBcryptPasswordEncoder.php';
require_once 'Services/Password/test/ilPasswordBaseTest.php';

use org\bovigo\vfs;

/**
 * Class ilBcryptPasswordEncoderTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPasswordEncoderTest extends ilPasswordBaseTest
{
    /**
     * @var string
     */
    const VALID_COSTS = '08';

    /**
     * @var string
     */
    const PASSWORD = 'password';

    /**
     * @var string
     */
    const WRONG_PASSWORD = 'wrong_password';

    /**
     * @var string
     */
    const CLIENT_SALT = 'homer!12345_/';

    /**
     * @var string
     */
    const PASSWORD_SALT = 'salt';

    /**
     * @var vfs\vfsStreamDirectory
     */
    protected $test_directory;
    
    /**
     * @var string
     */
    protected $test_directory_url;

    /**
     * @return vfs\vfsStreamDirectory
     */
    public function getTestDirectory()
    {
        return $this->test_directory;
    }

    /**
     * @param vfs\vfsStreamDirectory $test_directory
     */
    public function setTestDirectory($test_directory)
    {
        $this->test_directory = $test_directory;
    }

    /**
     * @return string
     */
    public function getTestDirectoryUrl()
    {
        return $this->test_directory_url;
    }

    /**
     * @param string $test_directory_url
     */
    public function setTestDirectoryUrl($test_directory_url)
    {
        $this->test_directory_url = $test_directory_url;
    }

    /**
     * Setup
     */
    protected function setUp()
    {
        vfs\vfsStream::setup();
        $this->setTestDirectory(vfs\vfsStream::newDirectory('tests')->at(vfs\vfsStreamWrapper::getRoot()));
        $this->setTestDirectoryUrl(vfs\vfsStream::url('root/tests'));

        parent::setUp();
    }

    /**
     *
     */
    private function skipIfPhpVersionIsNotSupported()
    {
        if (version_compare(phpversion(), '5.3.7', '<')) {
            $this->markTestSkipped('Requires PHP >= 5.3.7');
        }
    }

    /**
     * @return array
     */
    public function costsProvider()
    {
        $data = array();
        for ($i = 4; $i <= 31; $i++) {
            $data[] = array($i);
        }
        return $data;
    }

    /**
     * @return ilBcryptPasswordEncoder
     */
    private function getInstanceWithConfiguredDataDirectory()
    {
        $encoder = new ilBcryptPasswordEncoder(array(
            'data_directory' => $this->getTestDirectoryUrl()
        ));

        return $encoder;
    }

    /**
     * @return ilBcryptPasswordEncoder
     */
    public function testInstanceCanBeCreated()
    {
        $security_flaw_ignoring_encoder = new ilBcryptPasswordEncoder(array(
            'ignore_security_flaw' => true,
            'data_directory' => $this->getTestDirectoryUrl()
        ));
        $this->assertTrue($security_flaw_ignoring_encoder->isSecurityFlawIgnored());

        $security_flaw_respecting_encoder = new ilBcryptPasswordEncoder(array(
            'ignore_security_flaw' => false,
            'data_directory' => $this->getTestDirectoryUrl()
        ));
        $this->assertFalse($security_flaw_respecting_encoder->isSecurityFlawIgnored());

        $encoder = new ilBcryptPasswordEncoder(array(
            'cost' => self::VALID_COSTS,
            'data_directory' => $this->getTestDirectoryUrl()
        ));
        $this->assertInstanceOf('ilBcryptPasswordEncoder', $encoder);
        $this->assertEquals(self::VALID_COSTS, $encoder->getCosts());
        $this->assertFalse($encoder->isSecurityFlawIgnored());
        $encoder->setClientSalt(self::CLIENT_SALT);

        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testCostsCanBeRetrievedWhenCostsAreSet(ilBcryptPasswordEncoder $encoder)
    {
        $encoder->setCosts(4);
        $this->assertEquals(4, $encoder->getCosts());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @expectedException ilPasswordException
     */
    public function testCostsCannotBeSetAboveRange(ilBcryptPasswordEncoder $encoder)
    {
        $this->assertException(ilPasswordException::class);
        $encoder->setCosts(32);
    }

    /**
     * @depends testInstanceCanBeCreated
     * @expectedException ilPasswordException
     */
    public function testCostsCannotBeSetBelowRange(ilBcryptPasswordEncoder $encoder)
    {
        $this->assertException(ilPasswordException::class);
        $encoder->setCosts(3);
    }

    /**
     * @depends      testInstanceCanBeCreated
     * @dataProvider costsProvider
     */
    public function testCostsCanBeSetInRange($costs, ilBcryptPasswordEncoder $encoder)
    {
        $encoder->setCosts($costs);
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testPasswordShouldBeCorrectlyEncodedAndVerified(ilBcryptPasswordEncoder $encoder)
    {
        $encoder->setCosts(self::VALID_COSTS);
        $encoded_password = $encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
        $this->assertTrue($encoder->isPasswordValid($encoded_password, self::PASSWORD, self::PASSWORD_SALT));
        $this->assertFalse($encoder->isPasswordValid($encoded_password, self::WRONG_PASSWORD, self::PASSWORD_SALT));
        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @expectedException ilPasswordException
     */
    public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(ilBcryptPasswordEncoder $encoder)
    {
        $this->assertException(ilPasswordException::class);
        $encoder->setCosts(self::VALID_COSTS);
        $encoder->encodePassword(str_repeat('a', 5000), self::PASSWORD_SALT);
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(ilBcryptPasswordEncoder $encoder)
    {
        $encoder->setCosts(self::VALID_COSTS);
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), self::PASSWORD_SALT));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderReliesOnSalts(ilBcryptPasswordEncoder $encoder)
    {
        $this->assertTrue($encoder->requiresSalt());
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderDoesNotSupportReencoding(ilBcryptPasswordEncoder $encoder)
    {
        $this->assertFalse($encoder->requiresReencoding('hello'));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testNameShouldBeBcrypt(ilBcryptPasswordEncoder $encoder)
    {
        $this->assertEquals('bcrypt', $encoder->getName());
    }

    /**
     * @expectedException ilPasswordException
     */
    public function testExceptionIsRaisedIfSaltIsMissingIsOnEncoding()
    {
        $this->assertException(ilPasswordException::class);
        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(null);
        $encoder->setCosts(self::VALID_COSTS);
        $encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
    }

    /**
     * @expectedException ilPasswordException
     */
    public function testExceptionIsRaisedIfSaltIsMissingIsOnVerification()
    {
        $this->assertException(ilPasswordException::class);
        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(null);
        $encoder->setCosts(self::VALID_COSTS);
        $encoder->isPasswordValid('12121212', self::PASSWORD, self::PASSWORD_SALT);
    }

    /**
     *
     */
    public function testInstanceCanBeCreatedAndInitializedWithClientSalt()
    {
        $this->getTestDirectory()->chmod(0777);
        vfs\vfsStream::newFile(ilBcryptPasswordEncoder::SALT_STORAGE_FILENAME)->withContent(self::CLIENT_SALT)->at($this->getTestDirectory());

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $this->assertEquals(self::CLIENT_SALT, $encoder->getClientSalt());
    }

    /**
     *
     */
    public function testClientSaltIsGeneratedWhenNoClientSaltExistsYet()
    {
        $this->getTestDirectory()->chmod(0777);

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $this->assertNotNull($encoder->getClientSalt());
    }

    /**
     * @expectedException ilPasswordException
     */
    public function testExceptionIsRaisedWhenClientSaltCouldNotBeGeneratedInCaseNoClientSaltExistsYet()
    {
        $this->assertException(ilPasswordException::class);
        $this->getTestDirectory()->chmod(0000);

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
    }

    /**
     *
     */
    public function testBackwardCompatibilityCanBeRetrievedWhenBackwardCompatibilityIsSet()
    {
        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setBackwardCompatibility(true);
        $this->assertTrue($encoder->isBackwardCompatibilityEnabled());
        $encoder->setBackwardCompatibility(false);
        $this->assertFalse($encoder->isBackwardCompatibilityEnabled());
    }

    /**
     *
     */
    public function testBackwardCompatibility()
    {
        $this->skipIfPhpVersionIsNotSupported();

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(self::CLIENT_SALT);
        $encoder->setBackwardCompatibility(true);
        $encoded_password = $encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
        $this->assertTrue($encoder->isPasswordValid($encoded_password, self::PASSWORD, self::PASSWORD_SALT));
        $this->assertEquals('$2a$', substr($encoded_password, 0, 4));

        $another_encoder = $this->getInstanceWithConfiguredDataDirectory();
        $another_encoder->setClientSalt(self::CLIENT_SALT);
        $another_encoder->setBackwardCompatibility(false);
        $another_encoded_password = $another_encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
        $this->assertEquals('$2y$', substr($another_encoded_password, 0, 4));
        $this->assertTrue($another_encoder->isPasswordValid($encoded_password, self::PASSWORD, self::PASSWORD_SALT));
    }

    /**
     * @expectedException ilPasswordException
     */
    public function testExceptionIsRaisedIfTheRawPasswordContainsA8BitCharacterAndBackwardCompatibilityIsEnabled()
    {
        $this->assertException(ilPasswordException::class);
        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(self::CLIENT_SALT);
        $encoder->setBackwardCompatibility(true);
        $encoder->encodePassword(self::PASSWORD . chr(195), self::PASSWORD_SALT);
    }

    /**
     *
     */
    public function testExceptionIsNotRaisedIfTheRawPasswordContainsA8BitCharacterAndBackwardCompatibilityIsEnabledWithIgnoredSecurityFlaw()
    {
        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(self::CLIENT_SALT);
        $encoder->setBackwardCompatibility(true);
        $encoder->setIsSecurityFlawIgnored(true);
        $encoder->encodePassword(self::PASSWORD . chr(195), self::PASSWORD_SALT);
    }
}
