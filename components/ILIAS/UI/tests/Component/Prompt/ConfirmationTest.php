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

require_once __DIR__ . '/../../../../../../vendor/composer/vendor/autoload.php';
require_once __DIR__ . '/../../Base.php';
require_once __DIR__ . '/../Input/Field/CommonFieldRendering.php';

use ILIAS\Data\URI;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\Prompt;
use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation\Component\MessageBox;
use ILIAS\UI\Implementation\Component\Listing\Entity as ListingEntity;

class ConfirmationTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    public function testConfirmationPutsEntityIdsIntoHiddenFormInputs(): void
    {
        $captured_url = null;
        $captured_inputs = null;
        $form = $this->getFormMock('https://example.com/go');

        $form_factory = $this->createMock(C\Input\Container\Form\Factory::class);
        $form_factory->method('standard')->willReturnCallback(
            function (string $url, array $inputs) use (&$captured_url, &$captured_inputs, $form) {
                $captured_url = $url;
                $captured_inputs = $inputs;

                return $form;
            }
        );

        $container_factory = $this->createMock(C\Input\Container\Factory::class);
        $container_factory->method('form')->willReturn($form_factory);

        $input_factory = $this->createMock(C\Input\Factory::class);
        $input_factory->method('container')->willReturn($container_factory);
        $input_factory->method('field')->willReturn($this->getFieldFactory());

        $factory = $this->getPromptFactory($input_factory);

        $url_builder = new URLBuilder(new URI('https://example.com/go'));
        [$url_builder, $token] = $url_builder->acquireParameters(['test'], 'entities');

        $confirmation = $factory->confirmation(
            $this->getEntityRetrieval(),
            $url_builder,
            $token,
            [11, 22],
            'question',
            'title',
        );

        $this->assertInstanceOf(C\Prompt\Confirmation::class, $confirmation);
        $this->assertIsString($captured_url);
        $this->assertStringNotContainsString('11', $captured_url);
        $this->assertStringNotContainsString('22', $captured_url);
        $this->assertStringNotContainsString($token->getName(), $captured_url);

        $this->assertIsArray($captured_inputs);
        $this->assertArrayHasKey($token->getName(), $captured_inputs);
        $group = $captured_inputs[$token->getName()];
        $this->assertInstanceOf(C\Input\Field\Group::class, $group);
        $this->assertSame(['11', '22'], array_values($group->getValue()));

        $this->assertInstanceOf(Prompt\Confirmation::class, $confirmation);
        $this->assertSame($form, $confirmation->getForm());
    }

    public function testGetDataReadsEntityIdsFromRequest(): void
    {
        $factory = $this->getPromptFactoryWithRealForms();

        $url_builder = new URLBuilder(new URI('https://example.com/go'));
        [$url_builder, $token] = $url_builder->acquireParameters(['test'], 'entities');

        $confirmation = $factory->confirmation(
            $this->getEntityRetrieval(),
            $url_builder,
            $token,
            [11, 22],
            'question',
            'title',
        );

        $this->assertInstanceOf(Prompt\Confirmation::class, $confirmation);

        $group = $confirmation->getForm()->getInputs()[$token->getName()];
        $this->assertInstanceOf(C\Input\Field\Group::class, $group);

        $body = [];
        foreach ($group->getInputs() as $hidden) {
            $body[$hidden->getName()] = $hidden->getValue();
        }

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($body);

        $this->assertSame(['11', '22'], $confirmation->withRequest($request)->getData());
        $this->assertSame([], $confirmation->getData());
    }

    public function testGetDataReturnsEmptyArrayWhenRequestHasNoIds(): void
    {
        $factory = $this->getPromptFactoryWithRealForms();

        $url_builder = new URLBuilder(new URI('https://example.com/go'));
        [$url_builder, $token] = $url_builder->acquireParameters(['test'], 'entities');

        $confirmation = $factory->confirmation(
            $this->getEntityRetrieval(),
            $url_builder,
            $token,
            [11, 22],
            'question',
            'title',
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([]);

        $this->assertSame([], $confirmation->withRequest($request)->getData());
    }

    private function getPromptFactory(C\Input\Factory $input_factory): Prompt\Factory
    {
        return new Prompt\Factory(
            new I\SignalGenerator(),
            new Prompt\State\Factory(),
            new ListingEntity\Factory(),
            new MessageBox\Factory(),
            $input_factory
        );
    }

    private function getPromptFactoryWithRealForms(): Prompt\Factory
    {
        $field_factory = $this->getFieldFactory();
        $form_factory = new I\Input\Container\Form\Factory($field_factory, new I\SignalGenerator());
        $container_factory = $this->createMock(C\Input\Container\Factory::class);
        $container_factory->method('form')->willReturn($form_factory);

        $input_factory = $this->createMock(C\Input\Factory::class);
        $input_factory->method('field')->willReturn($field_factory);
        $input_factory->method('container')->willReturn($container_factory);

        return $this->getPromptFactory($input_factory);
    }

    private function getFormMock(string $post_url): I\Input\Container\Form\Standard
    {
        $form = $this->getMockBuilder(I\Input\Container\Form\Standard::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPostURL', 'getSubmitSignal'])
            ->getMock();
        $form->method('getPostURL')->willReturn($post_url);
        $form->method('getSubmitSignal')->willReturn(new Signal('submit_signal'));

        return $form;
    }

    private function getEntityRetrieval(): C\Entity\EntityRetrieval
    {
        return new class () implements C\Entity\EntityRetrieval {
            public function getEntities(
                ILIAS\UI\Factory $ui_factory,
                ILIAS\Data\Range $range,
                ILIAS\Data\Order $order,
                mixed $additional_viewcontrol_data,
                mixed $filter_data,
                mixed $additional_parameters,
            ): Generator {
                yield from [];
            }

            public function getEntitiesByIds(
                ILIAS\UI\Factory $ui_factory,
                ILIAS\Data\Order $order,
                array $entity_ids,
            ): Generator {
                yield from [];
            }
        };
    }

    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function messageBox(): MessageBox\Factory
            {
                return new MessageBox\Factory();
            }

            public function button(): I\Button\Factory
            {
                return new I\Button\Factory();
            }
        };
    }
}
