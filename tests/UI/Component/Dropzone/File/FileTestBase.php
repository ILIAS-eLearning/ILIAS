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

namespace ILIAS\Tests\UI\Component\Dropzone\File;

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\UI\Implementation as I;
use ILIAS\UI\Component as C;
use IncrementalSignalGenerator;
use ILIAS_UI_TestBase;
use NoUIFactory;

/**
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class FileTestBase extends ILIAS_UI_TestBase
{
    protected const FILE_DROPZONE_POST_URL = 'https://test.com/action?param1=123&param2=456';

    protected C\Dropzone\File\Factory $factory;
    protected I\Component\SignalGeneratorInterface $generator;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->generator = new IncrementalSignalGenerator();
        $this->factory = new I\Component\Dropzone\File\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            $this->getInputFactory(),
            $this->getLanguage()
        );
    }

    public function getUIFactory(): NoUIFactory
    {
        return new class ($this->generator) extends NoUIFactory {
            protected I\Component\SignalGeneratorInterface $generator;

            public function __construct(I\Component\SignalGeneratorInterface $generator)
            {
                $this->generator = $generator;
            }

            public function legacy(string $content): C\Legacy\Legacy
            {
                return new I\Component\Legacy\Legacy($content, $this->generator);
            }

            public function button(): C\Button\Factory
            {
                return new I\Component\Button\Factory();
            }

            public function modal(): C\Modal\Factory
            {
                return new I\Component\Modal\Factory($this->generator);
            }

            public function symbol(): C\Symbol\Factory
            {
                return new I\Component\Symbol\Factory(
                    new I\Component\Symbol\Icon\Factory(),
                    new I\Component\Symbol\Glyph\Factory(),
                    new I\Component\Symbol\Avatar\Factory()
                );
            }
        };
    }

    protected function getUploadHandlerMock(): C\Input\Field\UploadHandler
    {
        return new class () implements C\Input\Field\UploadHandler {
            public function getFileIdentifierParameterName(): string
            {
                return 'fid';
            }

            public function getUploadURL(): string
            {
                return '';
            }

            public function getFileRemovalURL(): string
            {
                return '';
            }

            public function getExistingFileInfoURL(): string
            {
                return '';
            }

            public function getInfoForExistingFiles(array $file_ids): array
            {
                return [];
            }

            public function getInfoResult(string $identifier): ?FileInfoResult
            {
                return null;
            }
        };
    }

    protected function getIncrementalNameSource(): I\Component\Input\NameSource
    {
        return new class () implements I\Component\Input\NameSource {
            protected int $count = 0;

            public function getNewName(): string
            {
                return 'name_' . $this->count++;
            }
        };
    }

    protected function getInputFactory(): C\Input\Factory
    {
        return new I\Component\Input\Factory(
            $this->generator,
            $this->getFieldFactory(),
            new I\Component\Input\Container\Factory(
                new I\Component\Input\Container\Form\Factory(
                    $this->getFieldFactory()
                ),
                $this->createMock(I\Component\Input\Container\Filter\Factory::class),
                $this->createMock(I\Component\Input\Container\ViewControl\Factory::class)
            ),
            $this->createMock(I\Component\Input\ViewControl\Factory::class),
        );
    }

    protected function getFieldFactory(): C\Input\Field\Factory
    {
        return new I\Component\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            $this->generator,
            $this->createMock(\ILIAS\Data\Factory::class),
            $this->getRefinery(),
            $this->getLanguage()
        );
    }

    protected function getDropzoneHtml(C\Dropzone\File\File $dropzone): string
    {
        return $this->brutallyTrimHTML(
            $this->getDefaultRenderer()->render($dropzone)
        );
    }
}
