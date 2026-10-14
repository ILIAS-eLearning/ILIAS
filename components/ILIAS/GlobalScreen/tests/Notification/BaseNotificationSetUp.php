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

use ILIAS\DI\Container;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\NullProviderFactory;
use ILIAS\GlobalScreen\Scope\Notification\Factory\NotificationFactory;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Services;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Counter\Factory;

require_once('./vendor/composer/vendor/autoload.php');
require_once(__DIR__ . "/../../../UI/tests/Base.php");

/**
 * Class BaseNotificationSetUp
 *
 * Some base Notification Work to be used in other tests for convenience
 */
abstract class BaseNotificationSetUp extends TestCase
{
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->identification = new IdentificationFactory(new NullProviderFactory());
        $this->provider = $this->createStub(NotificationProvider::class);
        $this->provider->method('getProviderNameForPresentation')->willReturn('Provider');

        $this->id = $this->identification->core($this->provider)->identifier('dummy');

        $this->factory = new NotificationFactory();
    }

    public function getUIFactory(): NoUIFactory
    {
        $language_mock = $this->createStub(\ILIAS\Language\Language::class);
        $language_mock->method('txt')->willReturnArgument(0);

        $sig_gen = $this->createStub(I\SignalGeneratorInterface::class);
        $sig_gen->method('create')->willReturn(new I\Signal("id"));

        return new class ($language_mock, $sig_gen) extends NoUIFactory {
            public function __construct(
                protected \ILIAS\Language\Language $language,
                protected I\SignalGeneratorInterface $sig_gen,
            ) {
            }

            public function item(): I\Item\Factory
            {
                return new I\Item\Factory();
            }

            public function symbol(): I\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory($this->language),
                    new I\Symbol\Avatar\Factory()
                );
            }

            public function mainControls(): I\MainControls\Factory
            {
                return new I\MainControls\Factory(
                    $this->sig_gen,
                    new I\MainControls\Slate\Factory(
                        $this->sig_gen,
                        new Factory(),
                        new I\Symbol\Factory(
                            new I\Symbol\Icon\Factory(),
                            new I\Symbol\Glyph\Factory($this->language),
                            new I\Symbol\Avatar\Factory()
                        )
                    )
                );
            }
        };
    }

    public function getDIC(): Container
    {
        $mocks = [
            'ui' => $this->createStub(UIServices::class),
            'ui.factory' => $this->createStub(\ILIAS\UI\Factory::class),
            'provider_factory' => $this->createStub(ProviderFactory::class),
        ];
        return new class ($mocks) extends Container {
            public function globalScreen(): Services
            {
                return new Services($this['provider_factory'], $this['ui']);
            }
        };
    }

    public function getDummyNotificationsProviderWithNotifications($notifications): AbstractNotificationProvider
    {
        $dic = $this->getDIC();
        $provider = new class ($dic) extends AbstractNotificationProvider {
            public array $notifications = [];

            public function getNotifications(): array
            {
                return $this->notifications;
            }
        };
        $provider->notifications = $notifications;
        return $provider;
    }
}
