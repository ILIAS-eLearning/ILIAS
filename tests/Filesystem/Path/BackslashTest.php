<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\Filesystem\Provider\DelegatingFilesystemFactory;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\Provider\FlySystem\FlySystemFilesystemFactory;

/**
 * Class BackslashTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BackslashTest extends TestCase
{
    /**
     * @var DelegatingFilesystemFactory
     */
    protected $factory;
    /**
     * @var \ILIAS\Filesystem\Filesystem
     */
    protected $file_system;

    protected function setUp() : void
    {
        parent::setUp();
        $this->factory = new FlySystemFilesystemFactory();
        $this->file_system = $this->factory->getLocal(new LocalConfig(__DIR__ . '/local'));
    }

    public function testFlatContent()
    {
        $flat = $this->file_system->listContents('');
        $contents = $this->mapPathNames($flat);
        $contents = $this->filterDSStore($contents);

        $this->assertEquals([
            'directoryOne',
            'directoryTwo'
        ], $contents);
    }

    public function testRecursiveContent()
    {
        $recursive = $this->file_system->listContents('', true);
        $contents = $this->mapPathNames($recursive);
        $contents = $this->filterDSStore($contents);

        $expected = [
            'directoryOne',
            'directoryOne/FileOneOne.txt',
            'directoryOne/FileOneTwo.txt',
            'directoryTwo',
            'directoryTwo/FileTwo//\'Two.txt',
//            'directoryTwo/FileTwo\\\\\'Two.txt', // unfortunatelly github actions cannot handle the backslashes as well :-(
            'directoryTwo/FileTwo//One.txt'
//            'directoryTwo/FileTwo\\\\One.txt' // unfortunatelly github actions cannot handle the backslashes as well :-(
        ];
        sort($expected);
        sort($contents);
        $this->assertEquals($expected, $contents);
    }

    public function testBackSlashFile()
    {
        $file = 'directoryTwo/FileTwo\\\\\'Two.txt';
        $file_content = $this->file_system->read($file);
        $this->assertEquals("CONTENT", $file_content);
    }

    public function testRealBackSlashFile()
    {
        $file = 'directoryTwo\\FileTwo\\\\\'Two.txt';
        $file_content = $this->file_system->read($file);
        $this->assertEquals("CONTENT", $file_content);
    }

    private function filterDSStore(array $d) : array
    {
        return array_filter($d, function (string $path) : bool {
            return strpos($path, '.DS_Store') === false;
        });
    }

    private function mapPathNames(array $d) : array
    {
        return array_map(function (\ILIAS\Filesystem\DTO\Metadata $m) {
            return $m->getPath();
        }, $d);
    }
}

