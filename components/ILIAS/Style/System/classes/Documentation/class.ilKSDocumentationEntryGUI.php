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

use ILIAS\UI\Implementation\Crawler\Entry as Entry;
use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntries as Entries;
use ILIAS\UI\Component\Panel\Report;

/**
 * Renders the Overview of one Example in the Administration
 */
class ilKSDocumentationEntryGUI
{
    protected Entry\ComponentEntry $entry;
    protected Entry\ComponentEntries $entries;
    protected ilCtrl $ctrl;
    protected ILIAS\UI\Factory $f;

    public function __construct(
        ILIAS\UI\Factory $factory,
        ilCtrl $ilCtrl,
        Entries $entries,
        ?string $current_opened_node_id
    ) {
        $this->f = $factory;
        $this->ctrl = $ilCtrl;

        if ($current_opened_node_id) {
            $this->ctrl->setParameterByClass('ilsystemstyledocumentationgui', 'node_id', $current_opened_node_id);
            $this->entry = $entries->getEntryById($current_opened_node_id);
        } else {
            $this->entry = $entries->getRootEntry();
        }
        $this->entries = $entries;
    }

    public function createUIComponentOfEntry(): Report
    {
        $sub_panels = [];

        $feature_wiki_links = [];
        foreach ($this->entry->getFeatureWikiReferences() as $href) {
            $feature_wiki_links[] = $href;
        }

        $sub_panels[] = $this->f->panel()->sub(
            'Description',
            [
                $this->f->listing()->descriptive(
                    [
                        'Purpose' => $this->escapeForHtml($this->entry->getDescription()->getProperty('purpose')),
                        'Composition' => $this->escapeForHtml($this->entry->getDescription()->getProperty('composition')),
                        'Effect' => $this->escapeForHtml($this->entry->getDescription()->getProperty('effect')),

                    ]
                ),
                $this->f->listing()->descriptive(
                    [
                        'Background' => $this->escapeForHtml($this->entry->getBackground()),
                        'Context' => $this->f->listing()->ordered($this->escapeForHtml($this->entry->getContext())),
                        'Feature Wiki References' => $this->f->listing()->ordered($this->escapeForHtml($feature_wiki_links))
                    ]
                )
            ]
        );

        if (sizeof($this->entry->getDescription()->getProperty('rivals'))) {
            $sub_panels[] = $this->f->panel()->sub(
                'Rivals',
                $this->f->listing()->descriptive(
                    $this->escapeForHtml($this->entry->getDescription()->getProperty('rivals'))
                )
            );
        }

        if ($this->entry->getRules() && $this->entry->getRules()->hasRules()) {
            $rule_listings = [];
            foreach ($this->entry->getRulesAsArray() as $categoery => $category_rules) {
                $rule_listings[ucfirst($categoery)] = $this->f->listing()->ordered($this->escapeForHtml($category_rules));
            }

            $sub_panels[] = $this->f->panel()->sub(
                'Rules',
                $this->f->listing()->descriptive($rule_listings)
            );
        }

        $examples = $this->entry->getExamples();
        if (count($examples) > 0) {
            $nr = 1;
            foreach ($this->entry->getExamples() as $name => $path) {
                $path = ILIAS_ABSOLUTE_PATH . "/$path";
                include_once($path);
                $title = 'Example ' . $nr . ': ' . ucfirst(str_replace('_', ' ', $name));
                $nr++;
                $examples_function_name = $this->entry->getExamplesNamespace() . '\\' . $name;
                try {
                    $example = "<div class='well'>" . $examples_function_name() . '</div>'; //Executes function loaded in file indicated by 'path'
                } catch (\ILIAS\UI\NotImplementedException $e) {
                    $example = "<div class='well'>This component is not yet fully implemented.</div>";
                }
                $content_part_1 = $this->f->legacy()->content($example);
                $code = str_replace('<?php\n', '', file_get_contents($path));
                $code_html = "<div class='code-container'><pre><code>" . htmlspecialchars($code) . '</code></pre></div>';
                $content_part_2 = $this->f->legacy()->content($code_html);
                $content = [$content_part_1, $content_part_2];
                $sub_panels[] = $this->f->panel()->sub($title, $content);
            }
        }
        $sub_panels[] = $this->f->panel()->sub('Relations', [
            $this->f->listing()->descriptive(
                [
                    'Parents' => $this->f->listing()->ordered(
                        $this->escapeForHtml($this->entries->getParentsOfEntryTitles($this->entry->getId()))
                    ),
                    'Descendants' => $this->f->listing()->unordered(
                        $this->escapeForHtml($this->entries->getDescendantsOfEntryTitles($this->entry->getId()))
                    )
                ]
            )
        ]);

        return $this->f->panel()
                       ->report($this->escapeForHtml($this->entry->getTitle()), $sub_panels);
    }

    private function escapeForHtml(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map($this->escapeForHtml(...), $value);
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
