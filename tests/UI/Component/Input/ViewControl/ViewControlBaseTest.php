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

use ILIAS\UI\Implementation\Component\Input\ViewControl as Control;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;

abstract class ViewControlBaseTest extends ILIAS_UI_TestBase
{
    protected function getNamesource()
    {
        return new class () implements NameSource {
            public int $count = 0;
            public function getNewName(): string
            {
                $name = "name_{$this->count}";
                $this->count++;

                return $name;
            }
        };
    }

    protected function buildDataFactory(): Data\Factory
    {
        return new Data\Factory();
    }

    protected function buildRefinery(): Refinery
    {
        return new Refinery(
            $this->buildDataFactory(),
            $this->createMock(ilLanguage::class)
        );
    }

    protected function buildFieldFactory(): FieldFactory
    {
        return new FieldFactory(
            $this->createMock(UploadLimitResolver::class),
            new SignalGenerator(),
            $this->buildDataFactory(),
            $this->buildRefinery(),
            $this->getLanguage()
        );
    }

    protected function buildVCFactory(): Control\Factory
    {
        return new Control\Factory(
            $this->buildFieldFactory(),
            $this->buildDataFactory(),
            $this->buildRefinery(),
            new SignalGenerator(),
            $this->getLanguage(),
        );
    }

    public function getUIFactory(): NoUIFactory
    {
        $factory = new class () extends NoUIFactory {
            public function button(): I\Button\Factory
            {
                return new I\Button\Factory(
                    new SignalGenerator()
                );
            }
            public function symbol(): ILIAS\UI\Component\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }
        };
        $factory->sig_gen = new SignalGenerator();
        return $factory;
    }

    public function getDataFactory(): Data\Factory
    {
        return $this->buildDataFactory();
    }
}
