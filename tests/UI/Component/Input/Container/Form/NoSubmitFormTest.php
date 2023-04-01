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
 */

declare(strict_types=1);

namespace ILIAS\Tests\UI\Component\Input\Container\Form;

use ILIAS\UI\Implementation\Component\Input\Container\Form\FormWithoutSubmitButton;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;

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
}

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class NoSubmitFormTest extends \ILIAS_UI_TestBase
{
    protected SignalGenerator $signal_generator;
    protected NameSource $namesource;
    protected Refinery $refinery;
    protected \ilLanguageMock $language;

    public function setUp(): void
    {
        $this->signal_generator = new \SignalGeneratorMock();
        $this->namesource = new InputNameSource();
        $this->language = $this->getLanguage();
        $this->refinery = new Refinery(
            new \ILIAS\Data\Factory(),
            $this->language
        );

        parent::setUp();
    }

    public function test_render(): void
    {
        $post_url = 'http://ilias.localhost/some_url?param1=foo&param2=bar';

        $dummy_input = $this->buildInputFactory()->text('test_label');

        $form = new FormWithoutSubmitButton(
            $this->signal_generator,
            $this->buildInputFactory(),
            $this->namesource,
            $post_url,
            [$dummy_input]
        );

        $expected_html =
            "<form id=\"id_1\" role=\"form\" class=\"il-standard-form form-horizontal\" enctype=\"multipart/form-data\" action=\"$post_url\" method=\"post\" novalidate=\"novalidate\">" .
            $dummy_input->getCanonicalName() .
            "</form>";

        $renderer = $this->getDefaultRenderer(null, [$dummy_input]);

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($renderer->render($form))
        );
    }

    public function test_render_with_required_inputs(): void
    {
        $post_url = 'http://ilias.localhost/some_url?param1=foo&param2=bar';
        $required_lang_var = 'required_field';

        $dummy_input = $this->buildInputFactory()->text('test_label')->withRequired(true);

        $form = new FormWithoutSubmitButton(
            $this->signal_generator,
            $this->buildInputFactory(),
            $this->namesource,
            $post_url,
            [$dummy_input]
        );

        $expected_html =
            "<form id=\"id_1\" role=\"form\" class=\"il-standard-form form-horizontal\" enctype=\"multipart/form-data\" action=\"$post_url\" method=\"post\" novalidate=\"novalidate\">" .
            $dummy_input->getCanonicalName() .
            "<div class=\"il-standard-form-footer clearfix\"><span class=\"asterisk\">*</span><span class=\"small\"> $required_lang_var</span></div>" .
            "</form>";

        $renderer = $this->getDefaultRenderer(null, [$dummy_input]);

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($renderer->render($form))
        );
    }

    public function test_render_with_error(): void
    {
        $post_url = 'http://ilias.localhost/some_url?param1=foo&param2=bar';
        $error_lang_var = 'ui_error_in_group';

        $dummy_input = $this->buildInputFactory()->text('test_label')->withAdditionalTransformation(
            $this->refinery->custom()->constraint(
                static function ($value): bool {
                    return false; // always fail for testing purposes.
                },
                'this message does not matter because the input will not be properly rendered anyways.'
            )
        );

        $form = new FormWithoutSubmitButton(
            $this->signal_generator,
            $this->buildInputFactory(),
            $this->namesource,
            $post_url,
            [$dummy_input]
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([
            'form_input_1' => '',
        ]);

        $form = $form->withRequest($request);
        $data = $form->getData();

        $expected_html =
            "<form id=\"id_1\" role=\"form\" class=\"il-standard-form form-horizontal\" enctype=\"multipart/form-data\" action=\"$post_url\" method=\"post\" novalidate=\"novalidate\">" .
            "<div class=\"help-block alert alert-danger\" role=\"alert\">$error_lang_var</div>" .
            $dummy_input->getCanonicalName() .
            "</form>";

        $renderer = $this->getDefaultRenderer(null, [$dummy_input]);

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
}
