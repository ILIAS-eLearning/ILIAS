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

namespace ILIAS\Tests\UI\Component\Input\Container\Form;

use ILIAS\UI\Implementation\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Language\Language;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\Button\Factory as ButtonFactory;

require_once(__DIR__ . "/../../../../Base.php");

class InputNameSource implements NameSource
{
    public int $count = 0;

    public function getNewName(): string
    {
        $name = "form_input_$this->count";
        $this->count++;

        return $name;
    }

    public function getNewDedicatedName(string $dedicated_name): string
    {
        $name = $dedicated_name . "_$this->count";
        $this->count++;

        return $name;
    }
}

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class FormWithoutSubmitButtonsTest extends \ILIAS_UI_TestBase
{
    protected SignalGenerator $signal_generator;
    protected NameSource $namesource;
    protected Refinery $refinery;
    protected Language $language;
    private ButtonFactory $button_factory;

    public function setUp(): void
    {
        $this->signal_generator = new \SignalGeneratorMock();
        $this->namesource = new InputNameSource();
        $this->language = $this->getLanguage();
        $this->refinery = new Refinery(
            new \ILIAS\Data\Factory(),
            $this->language
        );

        $this->button_factory = new ButtonFactory();

        parent::setUp();
    }

    public function testRender(): void
    {
        $post_url = 'http://ilias.localhost/some_url?param1=foo&param2=bar';

        $dummy_input = $this->buildInputFactory()->text('test_label');

        $form = new StandardForm(
            $this->signal_generator,
            $this->buildInputFactory(),
            $this->namesource,
            $post_url,
            [$dummy_input]
        );

        $expected_html =
            "<form id=\"id_1\" class=\"c-form c-form--horizontal\" enctype=\"multipart/form-data\" action=\"$post_url\" method=\"post\">" .
            $dummy_input->getCanonicalName() .
            "</form>";

        $context = $this->createMock(\ILIAS\UI\Component\Modal\RoundTrip::class);
        $context->method('getCanonicalName')->willReturn('RoundTripModal');
        $renderer = $this->getDefaultRenderer(null, [$dummy_input], [$context]);

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($renderer->render($form))
        );
    }

    public function testRenderWithRequiredInputs(): void
    {
        $post_url = 'http://ilias.localhost/some_url?param1=foo&param2=bar';
        $required_lang_var = 'required_field';

        $dummy_input = $this->buildInputFactory()->text('test_label')->withRequired(true);

        $form = new StandardForm(
            $this->signal_generator,
            $this->buildInputFactory(),
            $this->namesource,
            $post_url,
            [$dummy_input]
        );

        $expected_html =
            "<form id=\"id_1\" class=\"c-form c-form--horizontal\" enctype=\"multipart/form-data\" action=\"$post_url\" method=\"post\">" .
            $dummy_input->getCanonicalName()
            . "<div class=\"c-form__footer\">"
            . "<div class=\"c-form__required\"><span class=\"asterisk\">*</span><span class=\"small\"> $required_lang_var</span></div>"
            . "</div>"
            . "</form>";

        $context = $this->createMock(\ILIAS\UI\Component\Modal\RoundTrip::class);
        $context->method('getCanonicalName')->willReturn('RoundTripModal');
        $renderer = $this->getDefaultRenderer(null, [$dummy_input], [$context]);

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($renderer->render($form))
        );
    }

    public function testRenderWithError(): void
    {
        $post_url = 'http://ilias.localhost/some_url?param1=foo&param2=bar';
        $error_lang_var = 'ui_error';
        $error_lang_var_in_group = 'ui_error_in_group';

        $dummy_input = $this->buildInputFactory()->text('test_label')->withAdditionalTransformation(
            $this->refinery->custom()->constraint(
                static function ($value): bool {
                    return false; // always fail for testing purposes.
                },
                'this message does not matter because the input will not be properly rendered anyways.'
            )
        );

        $form = new StandardForm(
            $this->signal_generator,
            $this->buildInputFactory(),
            $this->namesource,
            $post_url,
            [$dummy_input]
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([
            'form_0/form_input_1' => '',
        ]);

        $form = $form->withRequest($request);
        $data = $form->getData();

        $expected_html = <<<EOT
<form id="id_1" class="c-form c-form--horizontal" enctype="multipart/form-data" action="$post_url" method="post">
    <div class="c-form__error-msg alert alert-danger"><span class="sr-only">$error_lang_var:</span>$error_lang_var_in_group
    </div>{$dummy_input->getCanonicalName()}
</form>
EOT;

        $context = $this->createMock(\ILIAS\UI\Component\Modal\RoundTrip::class);
        $context->method('getCanonicalName')->willReturn('RoundTripModal');
        $renderer = $this->getDefaultRenderer(null, [$dummy_input], [$context]);

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($renderer->render($form))
        );
    }

    protected function buildInputFactory(): InputFactory
    {
        $df = new \ILIAS\Data\Factory();
        return new \ILIAS\UI\Implementation\Component\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            $this->signal_generator,
            $df,
            $this->refinery,
            $this->language
        );
    }

    public function getUIFactory(): \NoUIFactory
    {
        return new class ($this->button_factory) extends \NoUIFactory {
            public function __construct(
                protected ButtonFactory $button_factory,
            ) {
            }

            public function button(): ButtonFactory
            {
                return $this->button_factory;
            }
        };
    }
}
