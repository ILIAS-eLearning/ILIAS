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

namespace ILIAS\UI\examples\Input\Container;

use ilUIDemoFileUploadHandlerGUI;
use ilUIMarkdownPreviewGUI;

/**
 * ---
 * description: >
 *   ILIAS shows a Standard Form Container with many Input Fields to showcase
 *   the client-side abstraction of Input Containers.
 *
 * expected output: >
 *   ILIAS shows the Standard Form Container with its Input Fields, several of
 *   which are provided with existing values. Underneath two Standard Button's
 *   are visible. The first Button can be used to print the client-side
 *   representation of the Input Container to the browser's console on click.
 *   The console shows an Array instance with 22 entries afterwards.
 *   The second Button can be used to print some reduced format of the existing
 *   values to the browser's console on click. The console shows an Array
 *   instance with 12 entries afterwards.
 * ---
 */
function client(): string
{
    global $DIC;

    $button_factory = $DIC->ui()->factory()->button();
    $container_factory = $DIC->ui()->factory()->input()->container();
    $field_factory = $DIC->ui()->factory()->input()->field();
    $renderer = $DIC->ui()->renderer();

    $fields = [
        $field_factory->text('Text')->withValue('some value'),
        $field_factory->numeric('Numeric')->withValue(1_000),
        $field_factory->checkbox('Checkbox')->withValue(true),
        $field_factory->textarea('Textarea'),
        $field_factory->password('Password'),
        $field_factory->select('Select', ['opt1' => 'Option 1']),
        $field_factory->multiSelect('MultiSelect', ['opt1' => 'Option 1', 'opt2' => 'Option 2']),
        $field_factory->tag('Tag', ['Tag1', 'Tag2']),
        $field_factory->radio('Radio')
                      ->withOption('A', 'A')
                      ->withOption('B', 'B')
                      ->withValue('A'),
        $field_factory->dateTime('DateTime')->withValue('2025-07-21 13:00'),
        $field_factory->duration('Duration')
                      ->withValue(['2025-07-21 13:00', '2025-07-22 13:00']),
        $field_factory->url('URL'),
        $field_factory->link('Link'),
        $field_factory->hidden(),
        $field_factory->colorSelect('Color'),
        $field_factory->rating('Rating'),
        $field_factory->file(new ilUIDemoFileUploadHandlerGUI(), 'File'),
        $field_factory->markdown(new ilUIMarkdownPreviewGUI(), 'Markdown'),

        $field_factory->group([
            $field_factory->text('Group Text'),
            $field_factory->numeric('Group Numeric'),
        ])->withValue(['some other text value', 1_000_000]),

        $field_factory->optionalGroup([
            $field_factory->text('Optional Text'),
            $field_factory->numeric('Optional Numeric'),
        ], 'Optional Group')->withValue(['yet again some other text value', 1]),

        $field_factory->switchableGroup([
            $field_factory->group([
                $field_factory->text('Option 1 Text'),
                $field_factory->numeric('Option 1 Numeric'),
            ], 'Option 1'),
            $field_factory->group([
                $field_factory->text('Option 2 Text'),
                $field_factory->numeric('Option 2 Numeric'),
            ], 'Option 2')
        ], 'Switchable Group')->withValue([1, ['more text', 100]]),

        $field_factory->section([
            $field_factory->text('Section Text'),
            $field_factory->numeric('Section Numeric'),
        ], 'Section'),
    ];

    $container = $container_factory->form()->standard('#', $fields);

    $log_button = $button_factory->standard('log original structure', '#')->withAdditionalOnLoadCode(
        static fn($id) => "
            const buttonElement = document.getElementById('$id');
            const formElement = buttonElement.parentElement.querySelector(':scope > form');
            const container = il.UI.Input.Container.get(formElement?.id);
            buttonElement?.addEventListener('click', () => {
                console.log(container?.getFields());
            });
        ",
    );

    $reduce_button = $button_factory->standard('log reduced structure', '#')->withAdditionalOnLoadCode(
        static fn($id) => "
            const buttonElement = document.getElementById('$id');
            const formElement = buttonElement.parentElement.querySelector(':scope > form');
            const container = il.UI.Input.Container.get(formElement?.id);
            buttonElement?.addEventListener('click', () => {
                console.log(container?.reduceFields());
            });
        ",
    );

    return $renderer->render([$container, $log_button, $reduce_button]);
}
