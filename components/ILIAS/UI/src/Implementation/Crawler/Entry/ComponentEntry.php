<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Crawler\Entry;

use JsonSerializable;

/**
 * Stores Information of UI Components parsed from YAML, examples and less files
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class ComponentEntry extends AbstractEntryPart implements JsonSerializable
{
    /**
     * @var string[]
     */
    protected array $children = array();

    protected string $id = "";
    protected string $title = "";
    protected bool $is_abstract = false;
    protected array $status_list_entry = array("Accepted", "Proposed", "To be revised");
    protected array $status_list_implementation = array("Implemented", "Partly implemented", "To be implemented");
    protected string $status_entry = "";
    protected string $status_implementation = "";
    protected ?ComponentEntryDescription $description = null;
    protected string $background = "";
    protected array $context = [];
    protected string $selector = "";
    protected array $feature_wiki_references = array();
    protected ?ComponentEntryRules $rules = null;
    protected ?string $parent = null;
    protected array $less_variables = array();
    protected string $path = "";
    protected ?array $examples = null;
    protected string $examples_path = "";
    protected string $examples_namespace = "";
    protected string $namesapce = "";

    public function __construct($entry_data)
    {
        parent::__construct();
        $this->assert()->isIndex('id', $entry_data);
        $this->setId($entry_data['id']);
        $this->assert()->isIndex('title', $entry_data);
        $this->setTitle($entry_data['title']);
        $this->assert()->isIndex('namespace', $entry_data);
        $this->setNamespace($entry_data['namespace']);
        $this->setIsAbstract((bool) $entry_data['abstract']);
        $this->setStatusEntry("Proposed");
        $this->setStatusImplementation("Partly implemented");
        if (array_key_exists('description', $entry_data)) {
            $this->setDescription(new ComponentEntryDescription($entry_data['description']));
        }
        if (array_key_exists('rules', $entry_data) && is_array($entry_data['rules'])) {
            $this->setRules(new ComponentEntryRules($entry_data['rules']));
        }

        $this->assert()->isIndex('path', $entry_data);
        $this->setPath($entry_data['path']);

        if (array_key_exists('background', $entry_data)) {
            $this->setBackground($entry_data['background']);
        }
        if (array_key_exists('context', $entry_data)) {
            $this->setContext($entry_data['context']);
        }
        if (array_key_exists('featurewiki', $entry_data)) {
            $this->setFeatureWikiReferences($entry_data['featurewiki']);
        }
        if (array_key_exists('parent', $entry_data)) {
            $this->setParent((string) $entry_data['parent']);
        }
        if (array_key_exists('children', $entry_data)) {
            $this->setChildren($entry_data['children']);
        }

        $this->readExamples();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->assert()->isString($id, false);
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->assert()->isString($title, false);
        $this->title = $title;
    }

    public function isAbstract(): bool
    {
        return $this->is_abstract;
    }

    public function setIsAbstract(bool $is_abstract): void
    {
        $this->is_abstract = $is_abstract;
    }

    public function getStatusEntry(): string
    {
        return $this->status_entry;
    }

    public function setStatusEntry(string $status_entry): void
    {
        $this->assert()->isString($status_entry);
        $this->status_entry = $status_entry;
    }

    public function getStatusImplementation(): string
    {
        return $this->status_implementation;
    }

    public function setStatusImplementation(string $status_implementation): void
    {
        $this->assert()->isString($status_implementation);

        $this->status_implementation = $status_implementation;
    }

    public function getDescription(): ?ComponentEntryDescription
    {
        return $this->description;
    }

    public function getDescriptionAsArray(): array
    {
        return $this->description->getDescription();
    }

    /**
     * @throws \ILIAS\UI\Implementation\Crawler\Exception\CrawlerException
     */
    public function setDescription(ComponentEntryDescription $description): void
    {
        $this->assert()->isTypeOf($description, ComponentEntryDescription::class);
        $this->description = $description;
    }

    public function getBackground(): string
    {
        return $this->background;
    }

    public function setBackground(string $background): void
    {
        $this->assert()->isString($background);
        $this->background = $background;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->assert()->isArray($context);
        $this->context = $context;
    }

    public function getFeatureWikiReferences(): array
    {
        return $this->feature_wiki_references;
    }

    public function setFeatureWikiReferences(array $feature_wiki_references): void
    {
        $this->assert()->isArray($feature_wiki_references);
        $this->feature_wiki_references = $feature_wiki_references;
    }

    public function getRules(): ?ComponentEntryRules
    {
        return $this->rules;
    }

    public function getRulesAsArray(): array
    {
        if ($this->rules) {
            return $this->rules->getRules();
        } else {
            return [];
        }
    }

    public function setRules(ComponentEntryRules $rules): void
    {
        $this->assert()->isTypeOf($rules, ComponentEntryRules::class);
        $this->rules = $rules;
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function setSelector(string $selector): void
    {
        $this->assert()->isString($selector);
        $this->selector = $selector;
    }

    public function setLessVariables(array $less_variables): void
    {
        $this->assert()->isArray($less_variables);
        $this->less_variables = $less_variables;
    }

    public function getLessVariables(): array
    {
        return $this->less_variables;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->assert()->isString($path);
        $this->path = $path;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function setParent(string $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return string[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param string[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function addChild(string $child): void
    {
        $this->children[] = $child;
    }

    /**
     * @param string[] $children
     */
    public function addChildren(array $children): void
    {
        $this->setChildren(array_merge($this->children, $children));
    }

    public function getExamples(): ?array
    {
        return $this->examples;
    }

    protected function readExamples(): void
    {
        $this->examples = array();
        $case_insensitive_path = $this->getCaseInsensitiveExampleFolder();
        if (is_dir($case_insensitive_path)) {
            foreach (scandir($case_insensitive_path) as $file_name) {
                $example_path = $this->getExamplesPath() . "/" . $file_name;
                if (is_file($example_path) && pathinfo($example_path)["extension"] == "php") {
                    $example_name = str_replace(".php", "", $file_name);
                    $this->examples[$example_name] = $example_path;
                }
            }
        }
    }

    /**
     * Note case handling of is dir is different from OS to OS, therefore while
     * reading the examples from the folders, we ignore the case of the folder.
     * See also #26451
     */
    protected function getCaseInsensitiveExampleFolder(): string
    {
        $parent_folder = dirname($this->getExamplesPath());

        if (is_dir($parent_folder)) {
            foreach (scandir($parent_folder) as $folder_name) {
                if (strtolower($folder_name) == strtolower(basename($this->getExamplesPath()))) {
                    return $parent_folder . "/" . $folder_name;
                }
            }
        }

        return "";
    }

    public function getExamplesPath(): string
    {
        if (!$this->examples_path) {
            $path_components = str_replace(
                "/Factory",
                "",
                str_replace("Component", "examples", $this->getPath())
            )
                . "/" . str_replace(" ", "", $this->getTitle());
            $path_array = self::array_iunique(explode("/", $path_components));
            $this->examples_path = implode("/", $path_array);
        }
        return $this->examples_path;
    }

    public function getExamplesNamespace(): string
    {
        if (!$this->examples_namespace) {
            $this->examples_namespace = str_replace(
                "/",
                "\\",
                str_replace("src/UI", "\ILIAS\UI", $this->getExamplesPath())
            );
        }
        return $this->examples_namespace;
    }

    public function getNamespace(): string
    {
        return $this->namesapce;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namesapce = $namespace;
    }

    private static function array_iunique(array $array): array
    {
        return array_intersect_key(
            $array,
            array_unique(array_map("StrToLower", $array))
        );
    }

    public function jsonSerialize(): array
    {
        $description = $this->getDescription();
        if ($description) {
            $description_serialized = $description->jsonSerialize();
        } else {
            $description_serialized = "";
        }

        $rules = $this->getRules();
        if ($rules) {
            $rules_serialized = $rules->jsonSerialize();
        } else {
            $rules_serialized = "";
        }
        return array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'abstract' => $this->isAbstract(),
            'status_entry' => $this->getStatusEntry(),
            'status_implementation' => $this->getStatusImplementation(),
            'description' => $description_serialized,
            'background' => $this->getBackground(),
            'context' => $this->getContext(),
            'selector' => $this->getSelector(),
            'feature_wiki_references' => $this->getFeatureWikiReferences(),
            'rules' => $rules_serialized,
            'parent' => $this->getParent(),
            'children' => $this->getChildren(),
            'less_variables' => $this->getLessVariables(),
            'path' => $this->getPath(),
            'namespace' => $this->getNamespace()
        );
    }
}
