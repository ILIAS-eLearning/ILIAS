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

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;

trait CommonFieldRendering
{
    protected function getFieldFactory(): I\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        return new I\Input\Field\Factory(
            $this->createMock(UploadLimitResolver::class),
            new SignalGenerator(),
            $df,
            new Refinery($df, $language),
            $language
        );
    }

    protected function render(FormInput $component): string
    {
        return $this->brutallyTrimHTML(
            $this->getDefaultRenderer()->render($component)
        );
    }

    protected function testWithError(FormInput $component): void
    {
        $error = "an_error";
        $expected = '<div class="c-input__error-msg alert alert-danger"';
        $expected2 = 'ui_error:</span>' . $error . '</div>';
        $html = $this->render($component->withError($error));
        $this->assertStringContainsString($expected, $html);
        $this->assertStringContainsString($expected2, $html);
    }

    protected function testWithRequired(FormInput $component): void
    {
        $expected = '<span class="asterisk" aria-label="required_field">*</span></label>';
        $this->assertStringContainsString($expected, $this->render($component->withRequired(true)));
    }

    protected function testWithNoByline(FormInput $component): void
    {
        $expected = '<div class="c-input__help-byline">';
        $this->assertStringNotContainsString($expected, $this->render($component));
    }

    protected function testWithDisabled(FormInput $component): void
    {
        $type = $this->getDefaultRenderer()->getComponentCanonicalNameAttribute($component);
        $expected = '<fieldset class="c-input" data-il-ui-component="' . $type . '" data-il-ui-input-name="name_0" disabled="disabled"';
        $this->assertStringContainsString($expected, $this->render($component->withDisabled(true)));
    }

    protected function testWithAdditionalOnloadCodeRendersId(FormInput $component): void
    {
        $component = $component->withAdditionalOnLoadCode(
            function (string $id): string {
                return '';
            }
        );

        $js_binding = new class () implements JavaScriptBinding {
            public function createId(): string
            {
                return 'THE COMPONENT ID';
            }
            public function addOnLoadCode(string $code): void
            {
            }
            public function getOnLoadCodeAsync(): string
            {
                return '';
            }
        };


        $renderer = $this->getDefaultRenderer($js_binding);
        $outerhtml = $this->brutallyTrimHTML($renderer->render($component));
        if (method_exists($component, 'getInputs')) {
            $innerhtml = $this->brutallyTrimHTML($renderer->render($component->getInputs()));
            $outerhtml = str_replace($innerhtml, '', $outerhtml);
        }

        $this->assertStringContainsString(
            'id="THE COMPONENT ID"',
            $outerhtml
        );
    }

    protected function getFormWrappedHtml(
        string $type,
        string $label,
        string $payload_field,
        ?string $byline = null,
        ?string $label_id = null,
        ?string $js_id = null,
        ?string $name = 'name_0',
    ): string {
        $label_id = $label_id ? " for=\"$label_id\"" : '';
        $tab = $label_id ? '' : ' tabindex="0"';
        $js_id = $js_id ? " id=\"$js_id\"" : '';
        if ($type === 'section-field-input') {
            $headline_tag_open = "<h2>";
            $headline_tag_close = "</h2>";
        } else {
            $headline_tag_open = "";
            $headline_tag_close = "";
        }

        $html = '
        <fieldset class="c-input" data-il-ui-component="' . $type . '" data-il-ui-input-name="' . $name . '"' . $js_id . $tab . '>
            <label' . $label_id . '>' . $headline_tag_open . $label . $headline_tag_close . '</label>
            <div class="c-input__field">';
        $html .= $payload_field;
        $html .= '
            </div>';
        if ($byline) {
            $html .= '
            <div class="c-input__help-byline">' . $byline . '</div>';
        }
        $html .= '
        </fieldset>
        ';
        return $this->brutallyTrimHTML($html);
    }
}
