<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\Provider\FlySystem\FlySystemLocalFilesystemFactory;
use ILIAS\FileUpload\Collection\ImmutableStringMap;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Handler\HandlerResult;
use PHPUnit\Framework\TestCase;

/**
 * The temp filesystem is a real one below a scratch directory, the session is the real, static ilSession.
 */
class ilLanguageImportUploadHandlerGUITest extends TestCase
{
    private const string SESSION_KEY = 'lang_ext_import_uploads';

    private string $root;
    private Filesystem $temp;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/lng_upload_test_' . bin2hex(random_bytes(6));
        mkdir($this->root);
        $this->temp = (new FlySystemLocalFilesystemFactory())->getInstance(new LocalConfig($this->root));
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        unset($GLOBALS['DIC']);
        $this->removeDir($this->root);
    }

    private function removeDir(string $dir): void
    {
        foreach (glob($dir . '/*') ?: [] as $entry) {
            is_dir($entry) ? $this->removeDir($entry) : unlink($entry);
        }
        rmdir($dir);
    }

    private function createHandler(?FileUpload $upload = null): ilLanguageImportUploadHandlerGUI
    {
        $handler = (new ReflectionClass(ilLanguageImportUploadHandlerGUI::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(ilLanguageImportUploadHandlerGUI::class, 'temp_system'))->setValue($handler, $this->temp);
        if ($upload !== null) {
            (new ReflectionProperty(\ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler::class, 'upload'))
                ->setValue($handler, $upload);
        }

        return $handler;
    }

    /** A file in the temp filesystem, issued to this session only if $own. */
    private function storeUpload(string $identifier, string $name, bool $own): void
    {
        $this->temp->createDir($identifier);
        $this->temp->put($identifier . '/' . $name, 'content');
        if ($own) {
            $_SESSION[self::SESSION_KEY] = [...($_SESSION[self::SESSION_KEY] ?? []), $identifier];
        }
    }

    public function testForeignAndUnknownIdentifiersAreNeitherReadNorDeleted(): void
    {
        $this->storeUpload('own', 'own.lang', true);
        $this->storeUpload('foreign', 'foreign.lang', false);
        $handler = $this->createHandler();

        self::assertSame('own.lang', $handler->getFileName('own'));
        foreach (['foreign', 'unknown', '', '../foreign'] as $identifier) {
            self::assertNull($handler->getFileName($identifier), $identifier);
            self::assertNull($handler->getInfoResult($identifier), $identifier);
            self::assertNull($handler->copyToLocalFile($identifier), $identifier);
        }
        self::assertSame([], $handler->getInfoForExistingFiles(['foreign', 'unknown']));

        $handler->discard('foreign');
        $handler->discard('unknown');

        self::assertTrue($this->temp->hasDir('foreign'));
        self::assertSame(['own'], $_SESSION[self::SESSION_KEY]);
    }

    public function testDiscardRemovesTheDirectoryAndForgetsTheIdentifier(): void
    {
        $this->storeUpload('a', 'a.lang', true);
        $this->storeUpload('b', 'b.lang', true);
        $handler = $this->createHandler();

        $handler->discard('a');

        self::assertFalse($this->temp->hasDir('a'));
        self::assertTrue($this->temp->hasDir('b'));
        self::assertSame(['b'], $_SESSION[self::SESSION_KEY]);
        self::assertNull($handler->getFileName('a'));
    }

    public function testDiscardAllKeepsTheExceptionsAndLeavesForeignUploadsAlone(): void
    {
        $this->storeUpload('keep', 'keep.lang', true);
        $this->storeUpload('drop1', 'drop1.lang', true);
        $this->storeUpload('drop2', 'drop2.lang', true);
        $this->storeUpload('foreign', 'foreign.lang', false);
        $handler = $this->createHandler();

        $handler->discardAll(['keep']);

        self::assertTrue($this->temp->hasDir('keep'));
        self::assertFalse($this->temp->hasDir('drop1'));
        self::assertFalse($this->temp->hasDir('drop2'));
        self::assertTrue($this->temp->hasDir('foreign'));
        self::assertSame(['keep'], array_values($_SESSION[self::SESSION_KEY]));

        $handler->discardAll();

        self::assertFalse($this->temp->hasDir('keep'));
        self::assertTrue($this->temp->hasDir('foreign'));
        self::assertSame([], $_SESSION[self::SESSION_KEY]);
    }

    public function testAnIssuedIdentifierWithoutFileIsNotResolved(): void
    {
        $_SESSION[self::SESSION_KEY] = ['gone'];
        $this->temp->createDir('empty');
        $_SESSION[self::SESSION_KEY][] = 'empty';
        $handler = $this->createHandler();

        self::assertNull($handler->getFileName('gone'));
        self::assertNull($handler->getFileName('empty'));
    }

    public function testASuccessfulUploadRemembersItsIdentifierInTheSession(): void
    {
        $source = $this->root . '/source.lang';
        file_put_contents($source, 'payload');
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $sanitizer = new class () {
            public function sanitize(string $name): string
            {
                return $name;
            }
        };
        $GLOBALS['DIC']['filesystem.security.sanitizing.filename'] = $sanitizer;

        $upload = $this->createStub(FileUpload::class);
        $upload->method('hasBeenProcessed')->willReturn(true);
        $upload->method('getResults')->willReturn([new UploadResult(
            'ilias_de.lang',
            7,
            'text/plain',
            $this->createStub(ImmutableStringMap::class),
            new ProcessingStatus(ProcessingStatus::OK, 'ok'),
            $source
        )]);
        $handler = $this->createHandler($upload);
        $method = new ReflectionMethod($handler, 'getUploadResult');

        $this->temp->createDir('previous');
        $_SESSION[self::SESSION_KEY] = ['previous'];
        $result = $method->invoke($handler);

        self::assertSame(HandlerResult::STATUS_OK, $result->getStatus());
        $identifier = $result->getFileIdentifier();
        self::assertNotSame('', $identifier);
        self::assertSame(['previous', $identifier], $_SESSION[self::SESSION_KEY]);
        self::assertSame('ilias_de.lang', $handler->getFileName($identifier));
    }

    public function testARejectedUploadIsReportedAndRemembersNothing(): void
    {
        $upload = $this->createStub(FileUpload::class);
        $upload->method('hasBeenProcessed')->willReturn(true);
        $upload->method('getResults')->willReturn([new UploadResult(
            'x.lang',
            1,
            'text/plain',
            $this->createStub(ImmutableStringMap::class),
            new ProcessingStatus(ProcessingStatus::REJECTED, 'too big'),
            $this->root . '/none'
        )]);
        $handler = $this->createHandler($upload);

        $result = (new ReflectionMethod($handler, 'getUploadResult'))->invoke($handler);

        self::assertSame(HandlerResult::STATUS_FAILED, $result->getStatus());
        self::assertSame('too big', $result->getMessage());
        self::assertArrayNotHasKey(self::SESSION_KEY, $_SESSION);
    }
}
