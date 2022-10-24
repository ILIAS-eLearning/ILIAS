<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use org\bovigo\vfs;

/**
 * Class ilBcryptPasswordEncoderTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPasswordEncoderTest extends ilPasswordBaseTest
{
    /** @var string */
    private const VALID_COSTS = '08';

    /** @var string */
    private const PASSWORD = 'password';

    /** @var string */
    private const WRONG_PASSWORD = 'wrong_password';

    /** @var string */
    private const CLIENT_SALT = 'homer!12345_/';

    /** @var string */
    private const PASSWORD_SALT = 'salt';

    protected vfs\vfsStreamDirectory $testDirectory;
    protected string $testDirectoryUrl;

    public function getTestDirectory(): vfs\vfsStreamDirectory
    {
        return $this->testDirectory;
    }

    public function setTestDirectory(vfs\vfsStreamDirectory $testDirectory): void
    {
        $this->testDirectory = $testDirectory;
    }

    public function getTestDirectoryUrl(): string
    {
        return $this->testDirectoryUrl;
    }

    public function setTestDirectoryUrl(string $testDirectoryUrl): void
    {
        $this->testDirectoryUrl = $testDirectoryUrl;
    }

    private function isVsfStreamInstalled(): bool
    {
        return class_exists('org\bovigo\vfs\vfsStreamWrapper');
    }

    private function skipIfvfsStreamNotSupported(): void
    {
        if (!$this->isVsfStreamInstalled()) {
            $this->markTestSkipped('Skipped test, vfsStream (https://github.com/bovigo/vfsStream) required');
        } else {
            vfs\vfsStream::setup();
            $this->setTestDirectory(vfs\vfsStream::newDirectory('tests')->at(vfs\vfsStreamWrapper::getRoot()));
            $this->setTestDirectoryUrl(vfs\vfsStream::url('root/tests'));
        }
    }

    /**
     * @return array<string, string[]>
     */
    public function costsProvider(): array
    {
        $data = [];
        for ($i = 4; $i <= 31; ++$i) {
            $data[sprintf('Costs: %s', $i)] = [(string) $i];
        }

        return $data;
    }

    private function getInstanceWithConfiguredDataDirectory(): ilBcryptPasswordEncoder
    {
        return new ilBcryptPasswordEncoder([
            'data_directory' => $this->getTestDirectoryUrl()
        ]);
    }

    public function testInstanceCanBeCreated(): ilBcryptPasswordEncoder
    {
        $this->skipIfvfsStreamNotSupported();

        $security_flaw_ignoring_encoder = new ilBcryptPasswordEncoder([
            'ignore_security_flaw' => true,
            'data_directory' => $this->getTestDirectoryUrl()
        ]);
        $this->assertTrue($security_flaw_ignoring_encoder->isSecurityFlawIgnored());

        $security_flaw_respecting_encoder = new ilBcryptPasswordEncoder([
            'ignore_security_flaw' => false,
            'data_directory' => $this->getTestDirectoryUrl()
        ]);
        $this->assertFalse($security_flaw_respecting_encoder->isSecurityFlawIgnored());

        $encoder = new ilBcryptPasswordEncoder([
            'cost' => self::VALID_COSTS,
            'data_directory' => $this->getTestDirectoryUrl()
        ]);
        $this->assertInstanceOf(ilBcryptPasswordEncoder::class, $encoder);
        $this->assertSame(self::VALID_COSTS, $encoder->getCosts());
        $this->assertFalse($encoder->isSecurityFlawIgnored());
        $encoder->setClientSalt(self::CLIENT_SALT);

        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testCostsCanBeRetrievedWhenCostsAreSet(ilBcryptPasswordEncoder $encoder): void
    {
        $expected = '04';

        $encoder->setCosts($expected);
        $this->assertSame($expected, $encoder->getCosts());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testCostsCannotBeSetAboveRange(ilBcryptPasswordEncoder $encoder): void
    {
        $this->expectException(ilPasswordException::class);
        $encoder->setCosts('32');
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testCostsCannotBeSetBelowRange(ilBcryptPasswordEncoder $encoder): void
    {
        $this->expectException(ilPasswordException::class);
        $encoder->setCosts('3');
    }

    /**
     * @doesNotPerformAssertions
     * @depends      testInstanceCanBeCreated
     * @dataProvider costsProvider
     * @throws ilPasswordException
     */
    public function testCostsCanBeSetInRange(string $costs, ilBcryptPasswordEncoder $encoder): void
    {
        $encoder->setCosts($costs);
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testPasswordShouldBeCorrectlyEncodedAndVerified(
        ilBcryptPasswordEncoder $encoder
    ): ilBcryptPasswordEncoder {
        $encoder->setCosts(self::VALID_COSTS);
        $encoded_password = $encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
        $this->assertTrue($encoder->isPasswordValid($encoded_password, self::PASSWORD, self::PASSWORD_SALT));
        $this->assertFalse($encoder->isPasswordValid($encoded_password, self::WRONG_PASSWORD, self::PASSWORD_SALT));

        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(
        ilBcryptPasswordEncoder $encoder
    ): void {
        $this->expectException(ilPasswordException::class);
        $encoder->setCosts(self::VALID_COSTS);
        $encoder->encodePassword(str_repeat('a', 5000), self::PASSWORD_SALT);
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(
        ilBcryptPasswordEncoder $encoder
    ): void {
        $encoder->setCosts(self::VALID_COSTS);
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), self::PASSWORD_SALT));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderReliesOnSalts(ilBcryptPasswordEncoder $encoder): void
    {
        $this->assertTrue($encoder->requiresSalt());
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderDoesNotSupportReencoding(ilBcryptPasswordEncoder $encoder): void
    {
        $this->assertFalse($encoder->requiresReencoding('hello'));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testNameShouldBeBcrypt(ilBcryptPasswordEncoder $encoder): void
    {
        $this->assertSame('bcrypt', $encoder->getName());
    }

    public function testExceptionIsRaisedIfSaltIsMissingIsOnEncoding(): void
    {
        $this->skipIfvfsStreamNotSupported();

        $this->expectException(ilPasswordException::class);
        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(null);
        $encoder->setCosts(self::VALID_COSTS);
        $encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
    }

    public function testExceptionIsRaisedIfSaltIsMissingIsOnVerification(): void
    {
        $this->skipIfvfsStreamNotSupported();

        $this->expectException(ilPasswordException::class);
        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(null);
        $encoder->setCosts(self::VALID_COSTS);
        $encoder->isPasswordValid('12121212', self::PASSWORD, self::PASSWORD_SALT);
    }

    public function testInstanceCanBeCreatedAndInitializedWithClientSalt(): void
    {
        $this->skipIfvfsStreamNotSupported();

        $this->getTestDirectory()->chmod(0777);
        vfs\vfsStream::newFile(ilBcryptPasswordEncoder::SALT_STORAGE_FILENAME)->withContent(self::CLIENT_SALT)->at($this->getTestDirectory());

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $this->assertSame(self::CLIENT_SALT, $encoder->getClientSalt());
    }

    public function testClientSaltIsGeneratedWhenNoClientSaltExistsYet(): void
    {
        $this->skipIfvfsStreamNotSupported();

        $this->getTestDirectory()->chmod(0777);

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $this->assertNotNull($encoder->getClientSalt());
    }

    public function testExceptionIsRaisedWhenClientSaltCouldNotBeGeneratedInCaseNoClientSaltExistsYet(): void
    {
        $this->skipIfvfsStreamNotSupported();

        $this->expectException(ilPasswordException::class);
        $this->getTestDirectory()->chmod(0000);

        $this->getInstanceWithConfiguredDataDirectory();
    }

    public function testBackwardCompatibilityCanBeRetrievedWhenBackwardCompatibilityIsSet(): void
    {
        $this->skipIfvfsStreamNotSupported();

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setBackwardCompatibility(true);
        $this->assertTrue($encoder->isBackwardCompatibilityEnabled());
        $encoder->setBackwardCompatibility(false);
        $this->assertFalse($encoder->isBackwardCompatibilityEnabled());
    }

    public function testBackwardCompatibility(): void
    {
        $this->skipIfvfsStreamNotSupported();

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(self::CLIENT_SALT);
        $encoder->setBackwardCompatibility(true);

        $encoded_password = $encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
        $this->assertTrue($encoder->isPasswordValid($encoded_password, self::PASSWORD, self::PASSWORD_SALT));
        $this->assertSame('$2a$', substr($encoded_password, 0, 4));

        $another_encoder = $this->getInstanceWithConfiguredDataDirectory();
        $another_encoder->setClientSalt(self::CLIENT_SALT);

        $another_encoder->setBackwardCompatibility(false);
        $another_encoded_password = $another_encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
        $this->assertSame('$2y$', substr($another_encoded_password, 0, 4));
        $this->assertTrue($another_encoder->isPasswordValid($encoded_password, self::PASSWORD, self::PASSWORD_SALT));
    }

    public function testExceptionIfPasswordsContainA8BitCharacterAndBackwardCompatibilityIsEnabled(): void
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
    public function testNoExceptionIfPasswordsContainA8BitCharacterAndBackwardCompatibilityIsEnabledWithIgnoredSecurityFlaw(): void
    {
        $this->skipIfvfsStreamNotSupported();

        $encoder = $this->getInstanceWithConfiguredDataDirectory();
        $encoder->setClientSalt(self::CLIENT_SALT);
        $encoder->setBackwardCompatibility(true);
        $encoder->setIsSecurityFlawIgnored(true);
        $encoder->encodePassword(self::PASSWORD . chr(195), self::PASSWORD_SALT);
    }
}
