<?php declare(strict_types=1);

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

use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\NullProviderFactory;
use ILIAS\GlobalScreen\Scope\Toast\Provider\AbstractToastProvider;
use ILIAS\GlobalScreen\Scope\Toast\Provider\ToastProvider;
use ILIAS\GlobalScreen\Scope\Toast\ToastServices;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ILIAS\GlobalScreen\Services;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Toast\Factory;
use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component as I;

require_once('./libs/composer/vendor/autoload.php');
require_once(__DIR__ . "/../../UI/Base.php");

abstract class BaseToastSetUp extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var ToastProvider */
    protected $provider;
    /** @var C\Toast\Factory */
    protected $factory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = \Mockery::mock(ToastProvider::class);
        $this->provider->shouldReceive('getProviderNameForPresentation')->andReturn('Provider');

        $this->factory = (new ToastServices())->factory();
    }

    public function getDIC(): ILIAS\DI\Container
    {
        $dic = new class () extends ILIAS\DI\Container {
            public function globalScreen(): Services
            {
                return new Services(Mockery::mock(ProviderFactory::class));
            }
        };
        return $dic;
    }

    public function getDummyToastProviderWithToasts($toasts): AbstractToastProvider
    {
        $dic = $this->getDIC();
        $provider = new class ($dic) extends AbstractToastProvider {
            public function getToasts(): array
            {
                return $this->toasts;
            }
        };
        $provider->toasts = $toasts;
        return $provider;
    }
}
