<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field;

use ILIAS\UI\Implementation\Render\ilTemplateWrapper;
use ILIAS\Data\URI;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Menu\Drilldown;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Renderer;

function resource_selector_prototype(): string
{
    global $DIC;

    $main_template = $DIC->ui()->mainTemplate();
    $renderer = $DIC->ui()->renderer();
    $factory = $DIC->ui()->factory();

    $prototype = getInputTemplate($main_template, 'tpl.resource_selector_prototype.html');

    $c_icon = $factory->symbol()->icon()->standard('', '')->withAbbreviation('C');
    $g_icon = $factory->symbol()->icon()->standard('', '')->withAbbreviation('G');

    $structure = [
        new PseudoEntry('1 Category', $c_icon, [
            new PseudoEntry('1.1 Sub Category', $c_icon, [
                new PseudoEntry('1.1.1 Course', $c_icon),
                new PseudoEntry('1.1.2 Group', $g_icon),
            ]),
            new PseudoEntry('1.2 Course', $c_icon),
            new PseudoEntry('1.3 Group', $g_icon),
        ]),
        new PseudoEntry('2 Category', $c_icon, [
            new PseudoEntry('2.1 Course', $c_icon),
            new PseudoEntry('2.2 Group', $g_icon),
        ]),
        new PseudoEntry(
            '3 Course with an extremely long title which should be handled by the component somehow in an unfrastrating manner, which IMO is not yet the case, right?' .
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vitae nisl eget nunc aliquam aliquet.' .
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vitae nisl eget nunc aliquam aliquet.' .
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vitae nisl eget nunc aliquam aliquet.',
            $c_icon
        ),
        new PseudoEntry('4 Group', $g_icon),
        new PseudoEntry('Test breadcrumb nesting', $c_icon, [
            new PseudoEntry('Level 1', $c_icon, [
                new PseudoEntry('Level 2', $c_icon, [
                    new PseudoEntry('Level 3', $c_icon, [
                        new PseudoEntry('Level 4', $c_icon, [
                            new PseudoEntry('Level 5', $c_icon, [
                                new PseudoEntry('Level 6', $c_icon, [
                                    new PseudoEntry('Level 7', $c_icon, [
                                        new PseudoEntry('Level 8', $c_icon, [
                                            new PseudoEntry('Level 9', $c_icon, [
                                                new PseudoEntry('Level 10', $c_icon, [
                                                    new PseudoEntry('You have reached the target, yay!', $c_icon),
                                                ]),
                                            ]),
                                        ]),
                                    ]),
                                ]),
                            ]),
                        ]),
                    ]),
                ]),
            ]),
        ]),
        ...generateManyEntries($c_icon),
    ];

    $html = '';
    foreach ($structure as $pseudo_entry) {
        $html .= renderPseudoEntry($main_template, $renderer, $pseudo_entry);
    }

    $prototype->setVariable('ENTRIES', $html);

    $prototype->setVariable('DROPDOWN', $renderer->render($factory->dropdown()->standard([
        $factory->button()->shy('Entry 1', '#'),
        $factory->button()->shy('Entry 1', '#'),
        $factory->button()->shy('Entry 1', '#'),
    ])));

    return $prototype->get();
}

function getNewContainerEntry(
    \ilGlobalTemplateInterface $main_template,
    Renderer $renderer,
    PseudoEntry $pseudo_entry,
    int $level = 0
): string {
    $entry = getInputTemplate($main_template, 'tpl.resource_selector_container_item.html');
    $entry->setVariable('ITEM_ICON', $renderer->render($pseudo_entry->icon));
    $entry->setVariable('ITEM_NAME', $pseudo_entry->title);
    $entry->setVariable('SELECT_ACTION', getSelectActionHtml($main_template));
    $entry->setVariable('MENU_LEVEL', $level);

    $sub_entries = $pseudo_entry->entries ?? [];
    foreach ($sub_entries as $sub_entry) {
        if (null === $sub_entry->entries) {
            $sub_entry_html = getNewStandardEntry($main_template, $renderer, $sub_entry);
        } else {
            $sub_entry_html = getNewContainerEntry($main_template, $renderer, $sub_entry, $level + 1);
        }

        $entry->setCurrentBlock('block_sub_entry');
        $entry->setVariable('SUB_ENTRY', $sub_entry_html);
        $entry->parseCurrentBlock();
    }

    return $entry->get();
}

function getNewStandardEntry(
    \ilGlobalTemplateInterface $main_template,
    Renderer $renderer,
    PseudoEntry $pseudo_entry,
): string {
    $entry = getInputTemplate($main_template, 'tpl.resource_selector_standard_item.html');
    $entry->setVariable('ITEM_ICON', $renderer->render($pseudo_entry->icon));
    $entry->setVariable('ITEM_NAME', $pseudo_entry->title);
    $entry->setVariable('SELECT_ACTION', getSelectActionHtml($main_template));
    return $entry->get();
}

function getSelectActionHtml(\ilGlobalTemplateInterface $main_template): string
{
    $action = getInputTemplate($main_template, 'tpl.resource_selector_select_action.html');
    return $action->get();
}

function renderPseudoEntry(
    \ilGlobalTemplateInterface $main_template,
    Renderer $renderer,
    PseudoEntry $pseudo_entry,
): string {
    if (null !== $pseudo_entry->entries) {
        return getNewContainerEntry($main_template, $renderer, $pseudo_entry);
    }

    return getNewStandardEntry($main_template, $renderer, $pseudo_entry);
}

function getInputTemplate(\ilGlobalTemplateInterface $main_template, string $relative_path): Template
{
    return new ilTemplateWrapper(
        $main_template,
        new \ilTemplate(
            __DIR__ . "/../../../templates/default/Input/$relative_path",
            true,
            false
        )
    );
}

function generateManyEntries(Symbol $pseudo_icon): array
{
    $array = [];
    foreach (range(0, 100) as $i) {
        $array[] = new PseudoEntry("Entry $i", $pseudo_icon);
    }

    return $array;
}

class PseudoEntry
{
    public function __construct(
        public string $title,
        public Symbol $icon,
        public ?array $entries = null,
    ) {
    }
}
