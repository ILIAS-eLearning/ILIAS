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

use ILIAS\GlobalScreen\Scope\Toast\Provider\AbstractToastProvider;
use ILIAS\GlobalScreen\Scope\Toast\Provider\ToastProvider;
use ILIAS\GlobalScreen\Scope\Toast\ToastServices;
use ILIAS\GlobalScreen\Services;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Scope\Toast\Factory\ToastFactory;

require_once('./libs/composer/vendor/autoload.php');
require_once(__DIR__ . "/../../UI/Base.php");

abstract class BaseToastSetUp extends TestCase
{
    private array $toasts = [];

    private \ILIAS\DI\UIServices $ui_mock;
    protected ToastProvider $provider;
    protected ToastFactory $factory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->ui_mock = $this->createMock(\ILIAS\DI\UIServices::class);
        $this->provider = $this->createMock(ToastProvider::class);
        $this->provider->expects($this->any())->method('getProviderNameForPresentation')->willReturn('Provider');
        $this->factory = (new ToastServices($this->ui_mock))->factory();
    }

    public function getDIC(): ILIAS\DI\Container
    {
        $mocks = [
            'ui' => $this->createMock(\ILIAS\DI\UIServices::class),
            'ui.factory' => $this->createMock(\ILIAS\UI\Factory::class),
            'provider_factory'=> $this->createMock(ProviderFactory::class),
        ];
        return new class ($mocks) extends ILIAS\DI\Container {
            public function globalScreen(): Services
            {
                return new Services($this['provider_factory'], $this['ui']);
            }
        };
    }

    public function getDummyToastProviderWithToasts(array $toasts): AbstractToastProvider
    {
        $dic = $this->getDIC();
        $provider = new class ($dic) extends AbstractToastProvider {
            public array $toasts;
            public function getToasts(): array
            {
                return $this->toasts;
            }
        };
        $provider->toasts = $toasts;
        return $provider;
    }
}
