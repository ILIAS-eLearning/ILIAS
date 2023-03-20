<?php

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

declare(strict_types=1);

namespace ILIAS\Tests\UI\Component\Dropzone\File;

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\UI\Implementation as I;
use ILIAS\UI\Component as C;

/**
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class FileTestBase extends \ILIAS_UI_TestBase
{
    protected C\Dropzone\File\Factory $factory;
    protected I\Component\Input\Field\File $input;
    protected string $input_html = 'test_file_input';

    public function setUp(): void
    {
        $this->input = $this->createMock(I\Component\Input\Field\File::class);
        $this->input->method('getCanonicalName')->willReturn($this->input_html);

        $group_mock = $this->createMock(I\Component\Input\Field\Group::class);
        $group_mock->method('withNameFrom')->willReturnSelf();

        $factory_mock = $this->createMock(C\Input\Field\Factory::class);
        $factory_mock->method('group')->willReturn($group_mock);

        $this->factory = new I\Component\Dropzone\File\Factory(
            new I\Component\SignalGenerator(),
            $factory_mock
        );

        parent::setUp();
    }
}
