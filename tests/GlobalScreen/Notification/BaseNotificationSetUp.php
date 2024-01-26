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

use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\NullProviderFactory;
use ILIAS\GlobalScreen\Scope\Notification\Factory\NotificationFactory;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ILIAS\GlobalScreen\Services;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\UI\Component as C;

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Counter\Factory;

require_once('./libs/composer/vendor/autoload.php');
require_once(__DIR__ . "/../../UI/Base.php");

/**
 * Class BaseNotificationSetUp
 *
 * Some base Notification Work to be used in other tests for convenience
 */
abstract class BaseNotificationSetUp extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var IdentificationInterface
     */
    protected $id;
    /**
     * @var NotificationProvider
     */
    protected $provider;
    /**
     * @var IdentificationFactory
     */
    protected $identification;
    /**
     * @var NotificationFactory
     */
    protected $factory;


    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->identification = new IdentificationFactory(new NullProviderFactory());
        $this->provider = \Mockery::mock(NotificationProvider::class);
        $this->provider->shouldReceive('getProviderNameForPresentation')->andReturn('Provider');

        $this->id = $this->identification->core($this->provider)->identifier('dummy');

        $this->factory = new NotificationFactory();
    }

    public function getUIFactory() : NoUIFactory
    {
        $factory = new class() extends NoUIFactory {
            public function item() : ILIAS\UI\Component\Item\Factory
            {
                return new I\Item\Factory();
            }
            public function symbol() : ILIAS\UI\Component\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }
            public function mainControls() : C\MainControls\Factory
            {
                return new I\MainControls\Factory(
                    $this->sig_gen,
                    new I\MainControls\Slate\Factory(
                        $this->sig_gen,
                        new Factory(),
                        new I\Symbol\Factory(
                            new I\Symbol\Icon\Factory(),
                            new I\Symbol\Glyph\Factory(),
                            new I\Symbol\Avatar\Factory()
                        )
                    )
                );
            }
        };

        $factory->sig_gen = Mockery::mock(I\SignalGeneratorInterface::class);
        $factory->sig_gen->shouldReceive("create")->andReturn(new I\Signal("id"));
        return $factory;
    }

    public function getDIC() : ILIAS\DI\Container
    {
        $mocks = [
            'ui' => $this->createMock(\ILIAS\DI\UIServices::class),
            'ui.factory' => $this->createMock(\ILIAS\UI\Factory::class),
            'provider_factory' => $this->createMock(ProviderFactory::class),
        ];
        return new class($mocks) extends ILIAS\DI\Container {
            public function globalScreen() : Services
            {
                return new Services($this['provider_factory']);
            }
        };
    }

    public function getDummyNotificationsProviderWithNotifications($notifications) : AbstractNotificationProvider
    {
        $dic = $this->getDIC();
        $provider = new class($dic) extends AbstractNotificationProvider {
            public function getNotifications() : array
            {
                return $this->notifications;
            }
        };
        $provider->notifications = $notifications;
        return $provider;
    }
}
