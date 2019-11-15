<?php declare(strict_types=1);
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
    /** @var string */
    const VALID_COSTS = '08';

    /** @var string */
    const PASSWORD = 'password';

    /** @var string */
    const WRONG_PASSWORD = 'wrong_password';

    /** @var string */
    const CLIENT_SALT = 'homer!12345_/';

    /** @var string */
    const PASSWORD_SALT = 'salt';

    /** @var vfs\vfsStreamDirectory */
    protected $testDirectory;

    /** @var string */
    protected $testDirectoryUrl;

    /**
     * @return vfs\vfsStreamDirectory
     */
    public function getTestDirectory() : vfs\vfsStreamDirectory
    {
        return $this->testDirectory;
    }

    /**
     * @param vfs\vfsStreamDirectory $testDirectory
     */
    public function setTestDirectory(vfs\vfsStreamDirectory $testDirectory) : void
    {
        $this->testDirectory = $testDirectory;
    }

    /**
     * @return string
     */
    public function getTestDirectoryUrl() : string
    {
        return $this->testDirectoryUrl;
    }

    /**
     * @param string $testDirectoryUrl
     */
    public function setTestDirectoryUrl(string $testDirectoryUrl) : void
    {
        $this->testDirectoryUrl = $testDirectoryUrl;
    }

    /**
     *
     */
    private function skipIfPhpVersionIsNotSupported() : void
    {
        if (version_compare(phpversion(), '5.3.7', '<')) {
            $this->markTestSkipped('Requires PHP >= 5.3.7');
        }
    }

    /**
     * @return bool
     */
    private function isVsfStreamInstalled() : bool
    {
        return class_exists('org\bovigo\vfs\vfsStreamWrapper');
    }

    /**
     *
     */
    private function skipIfvfsStreamNotSupported() : void
    {
        if (!$this->isVsfStreamInstalled()) {
            $this->markTestSkipped('Skipped test, vfsStream (http://vfs.bovigo.org) required');
        } else {
            vfs\vfsStream::setup();
            $this->setTestDirectory(vfs\vfsStream::newDirectory('tests')->at(vfs\vfsStreamWrapper::getRoot()));
            $this->setTestDirectoryUrl(vfs\vfsStream::url('root/tests'));
        }
    }

    /**
     * @return array
     */
    public function costsProvider() : array
    {
        $data = [];
        for ($i = 4; $i <= 31; $i++) {
            $data[sprintf("Costs: %s", (string) $i)] = [(string) $i];
        }

        return $data;
    }

    /**
     * @return ilBcryptPasswordEncoder
     * @throws ilPasswordException
     */
    private function getInstanceWithConfiguredDataDirectory() : ilBcryptPasswordEncoder
    {
        $encoder = new ilBcryptPasswordEncoder(array(
            'data_directory' => $this->getTestDirectoryUrl()
        ));

        return $encoder;
    }

    /**
     * @return ilBcryptPasswordEncoder
     * @throws ilPasswordException
     */
    public function testInstanceCanBeCreated() : ilBcryptPasswordEncoder
    {
        $this->skipIfvfsStreamNotSupported();

        $security_flaw_ignoring_encoder = new ilBcryptPasswordEncoder([
            'ignore_security_flaw' => true,
            'data_directory'       => $this->getTestDirectoryUrl()
        ]);
        $this->assertTrue($security_flaw_ignoring_encoder->isSecurityFlawIgnored());

        $security_flaw_respecting_encoder = new ilBcryptPasswordEncoder([
            'ignore_security_flaw' => false,
            'data_directory'       => $this->getTestDirectoryUrl()
        ]);
        $this->assertFalse($security_flaw_respecting_encoder->isSecurityFlawIgnored());

        $encoder = new ilBcryptPasswordEncoder([
            'cost'           => self::VALID_COSTS,
            'data_directory' => $this->getTestDirectoryUrl()
        ]);
        $this->assertInstanceOf('ilBcryptPasswordEncoder', $encoder);
        $this->assertEquals(self::VALID_COSTS, $encoder->getCosts());
        $this->assertFalse($encoder->isSecurityFlawIgnored());
        $encoder->setClientSalt(self::CLIENT_SALT);

        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testCostsCanBeRetrievedWhenCostsAreSet(ilBcryptPasswordEncoder $encoder) : void
    {
        $expected = '04';

        $encoder->setCosts($expected);
        $this->assertEquals($expected, $encoder->getCosts());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testCostsCannotBeSetAboveRange(ilBcryptPasswordEncoder $encoder) : void
    {
        $this->expectException(ilPasswordException::class);
        $encoder->setCosts('32');
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testCostsCannotBeSetBelowRange(ilBcryptPasswordEncoder $encoder) : void
    {
        $this->expectException(ilPasswordException::class);
        $encoder->setCosts('3');
    }

    /**
     * @doesNotPerformAssertions
     * @depends      testInstanceCanBeCreated
     * @dataProvider costsProvider
     * @param string                  $costs
     * @param ilBcryptPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testCostsCanBeSetInRange(string $costs, ilBcryptPasswordEncoder $encoder) : void
    {
        $encoder->setCosts($costs);
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPasswordEncoder $encoder
     * @return ilBcryptPasswordEncoder
     * @throws ilPasswordException
     */
    public function testPasswordShouldBeCorrectlyEncodedAndVerified(
        ilBcryptPasswordEncoder $encoder
    ) : ilBcryptPasswordEncoder {
        $encoder->setCosts(self::VALID_COSTS);
        $encoded_password = $encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
        $this->assertTrue($encoder->isPasswordValid($encoded_password, self::PASSWORD, self::PASSWORD_SALT));
        $this->assertFalse($encoder->isPasswordValid($encoded_password, self::WRONG_PASSWORD, self::PASSWORD_SALT));
        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(
        ilBcryptPasswordEncoder $encoder
    ) : void {
        $this->expectException(ilPasswordException::class);
        $encoder->setCosts(self::VALID_COSTS);
        $encoder->encodePassword(str_repeat('a', 5000), self::PASSWORD_SALT);
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(
        ilBcryptPasswordEncoder $encoder
    ) : void {
        $encoder->setCosts(self::VALID_COSTS);
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), self::PASSWORD_SALT));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPasswordEncoder $encoder
     */
    public function testEncoderReliesOnSalts(ilBcryptPasswordEncoder $encoder) : void
    {
        $this->assertTrue($encoder->requiresSalt());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPasswordEncoder $encoder
     */
    public function testEncoderDoesNotSupportReencoding(ilBcryptPasswordEncoder $encoder) : void
    {
        $this->assertFalse($encoder->requiresReencoding('hello'));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPasswordEncoder $encoder
     */
    public function testNameShouldBeBcrypt(ilBcryptPasswordEncoder $encoder) : void
    {
        $this->assertEquals('bcrypt', $encoder->getName());
    }

    /**
     * @throws ilPasswordException
     */
    public function testExceptionIsRaisedIfSaltIsMissingIsOnEncoding() : void
    {
        $this->skipIfvfsStreamNotSupported();

        $this->expectException(ilPasswordException::class);
        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(null);
        $encoder->setCosts(self::VALID_COSTS);
        $encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
    }

    /**
     * @throws ilPasswordException
     */
    public function testExceptionIsRaisedIfSaltIsMissingIsOnVerification() : void
    {
        $this->skipIfvfsStreamNotSupported();

        $this->expectException(ilPasswordException::class);
        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(null);
        $encoder->setCosts(self::VALID_COSTS);
        $encoder->isPasswordValid('12121212', self::PASSWORD, self::PASSWORD_SALT);
    }

    /**
     * @throws ilPasswordException
     */
    public function testInstanceCanBeCreatedAndInitializedWithClientSalt() : void
    {
        $this->skipIfvfsStreamNotSupported();

        $this->getTestDirectory()->chmod(0777);
        vfs\vfsStream::newFile(ilBcryptPasswordEncoder::SALT_STORAGE_FILENAME)->withContent(self::CLIENT_SALT)->at($this->getTestDirectory());

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $this->assertEquals(self::CLIENT_SALT, $encoder->getClientSalt());
    }

    /**
     * @throws ilPasswordException
     */
    public function testClientSaltIsGeneratedWhenNoClientSaltExistsYet() : void
    {
        $this->skipIfvfsStreamNotSupported();

        $this->getTestDirectory()->chmod(0777);

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $this->assertNotNull($encoder->getClientSalt());
    }

    /**
     * @throws ilPasswordException
     */
    public function testExceptionIsRaisedWhenClientSaltCouldNotBeGeneratedInCaseNoClientSaltExistsYet() : void
    {
        $this->skipIfvfsStreamNotSupported();

        $this->expectException(ilPasswordException::class);
        $this->getTestDirectory()->chmod(0000);

        $this->getInstanceWithConfiguredDataDirectory();
    }

    /**
     * @throws ilPasswordException
     */
    public function testBackwardCompatibilityCanBeRetrievedWhenBackwardCompatibilityIsSet() : void
    {
        $this->skipIfvfsStreamNotSupported();

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setBackwardCompatibility(true);
        $this->assertTrue($encoder->isBackwardCompatibilityEnabled());
        $encoder->setBackwardCompatibility(false);
        $this->assertFalse($encoder->isBackwardCompatibilityEnabled());
    }

    /**
     * @throws ilPasswordException
     */
    public function testBackwardCompatibility() : void
    {
        $this->skipIfPhpVersionIsNotSupported();
        $this->skipIfvfsStreamNotSupported();

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
     * @throws ilPasswordException
     */
    public function testExceptionIfPasswordsContainA8BitCharacterAndBackwardCompatibilityIsEnabled() : void
    {
        $this->skipIfvfsStreamNotSupported();

        $this->expectException(ilPasswordException::class);
        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(self::CLIENT_SALT);
        $encoder->setBackwardCompatibility(true);
        $encoder->encodePassword(self::PASSWORD . chr(195), self::PASSWORD_SALT);
    }

    /**
     * @doesNotPerformAssertions
     * @throws ilPasswordException
     */
    public function testNoExceptionIfPasswordsContainA8BitCharacterAndBackwardCompatibilityIsEnabledWithIgnoredSecurityFlaw() : void
    {
        $this->skipIfvfsStreamNotSupported();

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(self::CLIENT_SALT);
        $encoder->setBackwardCompatibility(true);
        $encoder->setIsSecurityFlawIgnored(true);
        $encoder->encodePassword(self::PASSWORD . chr(195), self::PASSWORD_SALT);
    }
}