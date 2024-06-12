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
        $expected = '<div class="c-input__error-msg alert alert-danger" aria-describedby="';
        $expected2 = '" role="alert">' . $error . '</div>';
        $html = $this->render($component->withError($error));
        $this->assertStringContainsString($expected, $html);
        $this->assertStringContainsString($expected2, $html);
    }

    protected function testWithRequired(FormInput $component): void
    {
        $expected = '<span class="asterisk">*</span></label>';
        $this->assertStringContainsString($expected, $this->render($component->withRequired(true)));
    }

    protected function testWithNoByline(FormInput $component): void
    {
        $expected = '<div class="c-input__help-byline">';
        $this->assertStringNotContainsString($expected, $this->render($component));
    }

    protected function testWithDisabled(FormInput $component): void
    {
        $type = str_replace(' ', '', $component->getCanonicalName());
        $expected = '<fieldset class="c-input" data-il-ui-type="' . $type . '" data-il-ui-name="name_0" disabled="disabled">';
        $this->assertStringContainsString($expected, $this->render($component->withDisabled(true)));
    }

    protected function getFormWrappedHtml(
        string $type,
        string $label,
        string $payload_field,
        ?string $byline = null,
        ?string $id = 'id_1',
        ?string $name = 'name_0'
    ): string {
        $id = $id ? " for=\"$id\"" : '';
        $html = '
        <fieldset class="c-input" data-il-ui-type="' . $type . '" data-il-ui-name="' . $name . '">
            <label' . $id . '>' . $label . '</label>
            <div class="c-input__field">';
        $html .= $payload_field;
        $html .= '
            </div>';
        if($byline) {
            $html .= '
            <div class="c-input__help-byline">' . $byline . '</div>';
        }
        $html .= '
        </fieldset>
        ';
        return $this->brutallyTrimHTML($html);
    }
}
