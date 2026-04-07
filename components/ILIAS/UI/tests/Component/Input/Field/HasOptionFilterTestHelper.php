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

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Implementation\Component\Symbol as S;
use ILIAS\UI\Implementation\Component as I;

trait HasOptionFilterTestHelper
{
    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function symbol(): I\Symbol\Factory
            {
                return new S\Factory(
                    new S\Icon\Factory(),
                    new S\Glyph\Factory(),
                    new S\Avatar\Factory()
                );
            }
        };
    }

    protected function testHasOptionFilter(I\Input\Field\HasOptionFilterInternal $component): void
    {
        $this->assertTrue($component->hasOptionFilter(), 'The component should be searchable.');

        /**
         * check if filter elements are present at all
         */
        $html = $this->renderInsideContainer($component);
        $expected1 = 'role="search"';
        $expected2 = 'c-field--has-option-filter__item';
        $this->assertStringContainsString($expected1, $html);
        $this->assertStringContainsString($expected2, $html);

        /**
         * compare rendered html of the filter field context
         * ABOVE and BELOW the nested field
         */
        // Id count varies depending on complexity of nested input. We have to strip them out.
        $strip_ids_fn = function (string $html): string {
            return preg_replace_callback(
                '/\b(id|for|aria-labelledby|aria-describedby|aria-controls)="id_\d+"/',
                function ($matches) {
                    return $matches[1] . '="id_stripped"';
                },
                $html
            );
        };
        $html = $strip_ids_fn($html);

        // getting html up to and after nested input
        $start_cut_anchor = 'c-input--has-option-filter__nothing-selected';
        $end_cut_anchor = '<span class="message-no-match';
        $start_pos = mb_strpos($html, $start_cut_anchor);
        $closing_div_pos = mb_strpos($html, '</div>', $start_pos);
        $cut_after_pos = $closing_div_pos + mb_strlen('</div>');
        $span_pos = mb_strpos($html, $end_cut_anchor, $cut_after_pos);

        $filter_before_nesting = $strip_ids_fn(parent::brutallyTrimHTML('<label>label</label><div class="c-input__field"><button type="button" class="c-input--has-option-filter__visibility-toggle btn btn-link" aria-controls="id_5" aria-expanded="false"><span class="text-expand"><span class="glyph" aria-hidden="true"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></span>ui_field_option_filter_show_all_options</span><span class="text-collapse" style="display: none;"><span class="glyph" aria-hidden="true"><span class="glyphicon glyphicon-triangle-left" aria-hidden="true"></span></span>ui_field_option_filter_show_less</span></button><div class="c-input--has-option-filter__search-input" role="search"><label for="id_2"><span class="label__text" id="id_3">ui_field_option_filter_search_in</span><span class="screen-reader-hint sr-only" id="id_4">ui_field_option_filter_screen_reader_hint</span><input id="id_2" type="text" role="searchbox" aria-labelledby="id_3" aria-describedby="id_4"></label><button type="button" class="c-input--has-option-filter__clear-search btn btn-link" aria-controls="id_2" style="display: none;"><span class="glyph" aria-hidden="true"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span></span>ui_field_option_filter_clear_search</button></div><div class="c-input--has-option-filter__field" id="id_5" role="region" aria-label="ui_field_option_filter_filtered_results_aria_label"><div class="c-input--has-option-filter__synopsis"><div class="result-count sr-only" role="status" aria-live="polite" style="display: none;">ui_field_option_filter_options_shown</div></div><div class="c-input--has-option-filter__nothing-selected">ui_field_option_filter_no_selection</div>'));
        $filter_after_nesting = $strip_ids_fn(parent::brutallyTrimHTML('<span class="message-no-match" style="display: none">ui_field_option_filter_no_match</span></div></div><div class="c-input__help-byline">byline</div></fieldset>'));
        $expected_before_nesting = mb_substr($html, 0, $cut_after_pos);
        $expected_after_nesting = mb_substr($html, $span_pos);

        $this->assertStringContainsString($filter_before_nesting, $expected_before_nesting);
        $this->assertEquals($filter_after_nesting, $expected_after_nesting);
    }
}
