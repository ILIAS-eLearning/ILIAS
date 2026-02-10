<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Container\Filter\Standard;

/**
 * ---
 * description: >
 *   Example showing a Filter Container and Filter Inputs with additional JavaScript on-load-code
 *   attached to them.
 *
 * expected output: >
 *   ILIAS shows the rendered Filter Component with several Filter Inputs. When opening the browser
 *   console, for each of the Filter Input, as well as the Filter Container, a log entry that refers
 *   to their ID will be visible.
 * ---
 */
function with_additional_on_load_code(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $pseudo_load_code = static fn($name) => static fn($id) => "console.log('Loaded $name with ID: ' + '$id');";

    $pseudo_options = [
        'A' => 'Option A',
        'B' => 'Option B',
        'C' => 'Option C',
    ];

    $filter_inputs = [
        $factory->input()->field()->multiSelect('multi-select', $pseudo_options)->withAdditionalOnLoadCode($pseudo_load_code('multi-select')),
        $factory->input()->field()->select('single-select', $pseudo_options)->withAdditionalOnLoadCode($pseudo_load_code('single-select')),
        $factory->input()->field()->duration('duration')->withAdditionalOnLoadCode($pseudo_load_code('duration')),
        $factory->input()->field()->dateTime('datetime')->withAdditionalOnLoadCode($pseudo_load_code('datetime')),
        $factory->input()->field()->numeric('numeric')->withAdditionalOnLoadCode($pseudo_load_code('numeric')),
        $factory->input()->field()->text('text')->withAdditionalOnLoadCode($pseudo_load_code('text')),
    ];

    $filter = $factory->input()->container()->filter()->standard(
        '#',
        '#',
        '#',
        '#',
        '#',
        '#',
        $filter_inputs,
        array_map(static fn() => true, $filter_inputs),
        true,
        true,
    )->withAdditionalOnLoadCode($pseudo_load_code('filter'));

    return $renderer->render($filter);
}
