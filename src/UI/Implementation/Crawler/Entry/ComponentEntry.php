<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Crawler\Entry;

/**
 * Stores Information of UI Components parsed from YAML, examples and less files
 * @author              Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class ComponentEntry extends AbstractEntryPart implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $id = "";

    /**
     * @var string
     */
    protected $title = "";

    /**
     * @var bool
     */
    protected $is_abstract = false;

    /**
     * @var array
     */
    protected $status_list_entry = array("Accepted", "Proposed", "To be revised");

    /**
     * @var array
     */
    protected $status_list_implementation = array("Implemented", "Partly implemented", "To be implemented");

    /**
     * @var string
     */
    protected $status_entry = "";

    /**
     * @var string
     */
    protected $status_implementation = "";

    /**
     * @var ComponentEntryDescription
     */
    protected $description = null;

    /**
     * @var string
     */
    protected $background = "";

    /**
     * @var string
     */
    protected $context = [];

    /**
     * @var string
     */
    protected $selector = "";

    /**
     * @var array
     */
    protected $feature_wiki_references = array();

    /**
     * @var ComponentEntryRules
     */
    protected $rules = null;

    /**
     * @var string
     */
    protected $parent = false;

    /**
     * @var string[]
     */
    protected $children = array();

    /**
     * @var array
     */
    protected $less_variables = array();

    /**
     * @var string
     */
    protected $path = "";

    /**
     * @var array
     */
    protected $examples = null;

    /**
     * @var string
     */
    protected $examples_path = "";

    /**
     * ComponentEntry constructor.
     * @param $entry_data
     */
    public function __construct($entry_data)
    {
        parent::__construct();
        $this->assert()->isIndex('id', $entry_data);
        $this->setId($entry_data['id']);
        $this->assert()->isIndex('title', $entry_data);
        $this->setTitle($entry_data['title']);
        $this->assert()->isIndex('abstract', $entry_data);
        $this->setIsAbstract($entry_data['abstract']);
        $this->setStatusEntry("Proposed");
        $this->setStatusImplementation("Partly implemented");
        if (array_key_exists('description', $entry_data)) {
            $this->setDescription(new ComponentEntryDescription($entry_data['description']));
        }
        if (array_key_exists('rules', $entry_data)) {
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
            $this->setParent($entry_data['parent']);
        }
        if (array_key_exists('children', $entry_data)) {
            $this->setChildren($entry_data['children']);
        }

        if (!$this->isAbstract()) {
            $this->readExamples();
        }
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->assert()->isString($id, false);
        $this->id = $id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->assert()->isString($title, false);
        $this->title = $title;
    }

    public function isAbstract() : bool
    {
        return $this->is_abstract;
    }

    public function setIsAbstract(bool $is_abstract)
    {
        $this->is_abstract = $is_abstract;
    }

    public function getStatusEntry() : string
    {
        return $this->status_entry;
    }

    public function setStatusEntry(string $status_entry)
    {
        $this->assert()->isString($status_entry);
        $this->status_entry = $status_entry;
    }

    public function getStatusImplementation() : string
    {
        return $this->status_implementation;
    }

    public function setStatusImplementation(string $status_implementation)
    {
        $this->assert()->isString($status_implementation);

        $this->status_implementation = $status_implementation;
    }

    public function getDescription() : ?ComponentEntryDescription
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getDescriptionAsArray()
    {
        return $this->description->getDescription();
    }

    /**
     * @param ComponentEntryDescription $description
     * @throws \ILIAS\UI\Implementation\Crawler\Exception\CrawlerException
     */
    public function setDescription(ComponentEntryDescription $description)
    {
        $this->assert()->isTypeOf($description, ComponentEntryDescription::class);
        $this->description = $description;
    }

    public function getBackground() : string
    {
        return $this->background;
    }

    public function setBackground(string $background)
    {
        $this->assert()->isString($background);
        $this->background = $background;
    }

    public function getContext() : array
    {
        return $this->context;
    }

    public function setContext(array $context)
    {
        $this->assert()->isArray($context);
        $this->context = $context;
    }

    public function getFeatureWikiReferences() : array
    {
        return $this->feature_wiki_references;
    }

    public function setFeatureWikiReferences(array $feature_wiki_references)
    {
        $this->assert()->isArray($feature_wiki_references);
        $this->feature_wiki_references = $feature_wiki_references;
    }

    public function getRules() : ?ComponentEntryRules
    {
        return $this->rules;
    }

    public function getRulesAsArray() : array
    {
        if ($this->rules) {
            return $this->rules->getRules();
        } else {
            return [];
        }
    }

    public function setRules(ComponentEntryRules $rules)
    {
        $this->assert()->isTypeOf($rules, ComponentEntryRules::class);
        $this->rules = $rules;
    }

    public function getSelector() : string
    {
        return $this->selector;
    }

    public function setSelector(string $selector)
    {
        $this->assert()->isString($selector);
        $this->selector = $selector;
    }

    public function setLessVariables(array $less_variables)
    {
        $this->assert()->isArray($less_variables);
        $this->less_variables = $less_variables;
    }

    public function getLessVariables() : array
    {
        return $this->less_variables;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function setPath(string $path)
    {
        $this->assert()->isString($path);
        $this->path = $path;
    }

    public function getParent() : string
    {
        return $this->parent;
    }

    public function setParent(string $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return \string[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param \string[] $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function addChild(string $child)
    {
        $this->children[] = $child;
    }

    /**
     * @param \string[] $children
     */
    public function addChildren($children)
    {
        $this->setChildren(array_merge($this->children, $children));
    }

    public function getExamples() : ?array
    {
        return $this->examples;
    }

    protected function readExamples()
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
    protected function getCaseInsensitiveExampleFolder() : string
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

    public function getExamplesPath()
    {
        if (!$this->examples_path) {
            $path_components =
                str_replace("Component", "examples", $this->getPath())
                . "/" . str_replace(" ", "", $this->getTitle());
            $path_array = self::array_iunique(explode("/", $path_components));
            $this->examples_path = implode("/", $path_array);
        }
        return $this->examples_path;
    }


    public function getExamplesNamespace()
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

    public function getNamespace() : string
    {
        return $this->namesapce;
    }

    public function setNamespace(string $namespace) : void
    {
        $this->namesapce = $namespace;
    }

    private static function array_iunique(array $array) : array
    {
        return array_intersect_key(
            $array,
            array_unique(array_map("StrToLower", $array))
        );
    }

    public function jsonSerialize() : array
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
            'path' => $this->getPath()
        );
    }
}
