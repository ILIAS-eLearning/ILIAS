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
use ILIAS\Data\Factory;

/**
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class FileTestBase extends \ILIAS_UI_TestBase
{
    protected C\Dropzone\File\Factory $factory;
    protected I\Component\Input\Field\File $input;
    private C\Button\Factory $button_factory;

    public function setUp(): void
    {
        $this->button_factory = new I\Component\Button\Factory();

        $signal_generator = new I\Component\SignalGenerator();
        $field_factory = new I\Component\Input\Field\Factory(
            $this->createMock(I\Component\Input\UploadLimitResolver::class),
            $signal_generator,
            $this->getDataFactory(),
            $this->getRefinery(),
            $this->getLanguage()
        );

        $this->factory = new I\Component\Dropzone\File\Factory(
            $signal_generator,
            $field_factory,
        );

        $this->input = $field_factory->file($this->createMock(C\Input\Field\UploadHandler::class), '');

        parent::setUp();
    }

    /**
     * Returns the factory with an actual implementation of the button factory.
     * This is needed for the modal-buttons.
     */
    public function getUIFactory(): \NoUIFactory
    {
        return new class ($this->button_factory) extends \NoUIFactory {
            public function __construct(
                protected C\Button\Factory $button_factory,
            ) {
            }

            public function button(): C\Button\Factory
            {
                return $this->button_factory;
            }
        };
    }
}
