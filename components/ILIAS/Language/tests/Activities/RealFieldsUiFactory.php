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

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Language;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactoryImpl;
use ILIAS\UI\Implementation\Component\Input\Field\Node\Factory as NodeFactory;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Gives Activity tests a UI factory whose `->input()->field()` returns REAL field objects, so that
 * GrindsFormInput::grind() can run withNameFrom()/withInput()/getContent() on them; a plain mock would turn
 * every call into a Result\Error. Same recipe as CommonFieldRendering::getFieldFactory() in the UI tests.
 */
trait RealFieldsUiFactory
{
    /**
     * `input()` is a mock returning the real field factory from `field()`, wired against real
     * `\ILIAS\Data\Factory`/`\ILIAS\Refinery\Factory`; `txt()` of the Language mock echoes the key.
     */
    protected function createRealFieldsUiFactory(): \ILIAS\UI\Factory
    {
        $data_factory = new DataFactory();
        $lng = $this->createMock(Language::class);
        $lng->method('txt')->willReturnArgument(0);
        $refinery = new RefineryFactory($data_factory, $lng);

        $field_factory = new FieldFactoryImpl(
            $this->createMock(NodeFactory::class),
            $this->createMock(UploadLimitResolver::class),
            new SignalGenerator(),
            $data_factory,
            $refinery,
            $lng
        );

        $input = $this->createMock(\ILIAS\UI\Component\Input\Factory::class);
        $input->method('field')->willReturn($field_factory);

        $ui = $this->createMock(\ILIAS\UI\Factory::class);
        $ui->method('input')->willReturn($input);

        return $ui;
    }
}
