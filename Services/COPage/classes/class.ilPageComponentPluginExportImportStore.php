<?php

declare(strict_types=1);

class ilPageComponentPluginExportImportStore
{
    private static ?self $instance;
    public static function getInstance()
    {
        global $DIC;
        return self::$instance ??= new self(
            $DIC["component.repository"]
        );
    }

    private array $plugin_dependencies = [];
    private $pc_versions = [];
    private $pc_properties = [];

    public function __construct(
        ilComponentRepository $component_repository,
    ) {
        foreach ($component_repository->getPluginSlotById("pgcp")->getActivePlugins() as $plugin) {
            $plugin_name = $plugin->getName();
            if ($plugin->supportsExport()) {

                $this->plugin_dependencies[$plugin_name] = [
                    "component" => "Plugins/" . $plugin_name,
                    "entity" => "pgcp",
                    "ids" => []
                ];
            }
        }
    }

    /**
     * Build the store id of a page content
     */
    public function buildContentId(
        string $parent_type,
        int $page_id,
        string $language,
        string $pc_id
    ) {
        return $parent_type . ':' . $page_id . ':' . $language . ':' . $pc_id;
    }

    /**
     * Get the store id of the mapped page content
     */
    public function getMappedContentId(string $id, ilImportMapping $mapping): string
    {
        $parts = explode(':', $id);
        $old_page_id = $parts[0] . ':' . $parts[1];
        $new_page_id = $mapping->getMapping("Services/COPage", 'pg', $old_page_id);

        return $new_page_id . ':' . $parts[2] . ':' . $parts[3];
    }

    public function getPluginDependencies(): array
    {
        return array_values($this->plugin_dependencies);
    }

    public function setPluginDependency(string $id, string $plugin_name): void
    {
        $this->plugin_dependencies[$plugin_name]['ids'][] = $id;
    }

    public function hasExportablePlugins(): bool
    {
        return !empty($this->plugin_dependencies);
    }

    public function isPluginExportable(string $plugin_name): bool
    {
        return isset($this->plugin_dependencies[$plugin_name]);
    }

    /**
     * Store the properties of a plugged page content
     * This method is used by ilCOPageExporter to provide the properties
     */
    public function setPCProperties(string $id, array $properties): void
    {
        $this->pc_properties[$id] = $properties;
    }

    /**
     * Get the properties of a plugged page content
     */
    public function getPCProperties(string $id): ?array
    {
        return $this->pc_properties[$id] ?? null;
    }

    /**
     * Store the version of a plugged page content
     */
    public function setPCVersion(string $id, string $version): void
    {
        $this->pc_versions[$id] = $version;
    }

    /**
     * Get the stored version of a plugged page content
     */
    public function getPCVersion(string $id)
    {
        return $this->pc_versions[$id] ?? null;
    }

    /**
     * Extract the properties of the plugged page contents
     * The page XML is scanned for plugged contents with own exporters
     * Their ids are added as dependencies
     *
     * Called from getXmlRepresentation() for each handled page object
     * Extracted data is used by dependent exporters afterward
     */
    public function extractPluginProperties(
        ilPageObject $a_page
    ): void {
        if (empty($this->plugin_dependencies)) {
            return;
        }

        $a_page->buildDom();
        $domdoc = $a_page->getDomDoc();
        $xpath = new DOMXPath($domdoc);
        $nodes = $xpath->query("//PageContent[child::Plugged]");

        /** @var DOMElement $pcnode */
        foreach ($nodes as $pcnode) {
            // page content id (unique in the page)
            $pc_id = $pcnode->getAttribute('PCID');
            $plnode = $pcnode->childNodes->item(0);
            $plugin_name = $plnode->getAttribute('PluginName');
            $plugin_version = $plnode->getAttribute('PluginVersion');

            // dependency should be exported
            if ($this->isPluginExportable($plugin_name)) {
                $properties = [];
                /** @var DOMElement $child */
                foreach ($plnode->childNodes as $child) {
                    $properties[$child->getAttribute('Name')] = $child->nodeValue;
                }

                $id = $this->buildContentId($a_page->getParentType(), $a_page->getId(), $a_page->getLanguage(), $pc_id);
                $this->setPCVersion($id, $plugin_version);
                $this->setPCProperties($id, $properties);
                $this->setPluginDependency($id, $plugin_name);
            }
        }
    }

    /**
     * Replace the properties of the plugged page contents
     * The page XML is scanned for plugged contents with own importers
     * The plugged content is replaced
     *
     * Called by finalProcessing() for each handled page
     * Extracted data is used by dependent plugin importers afterward
     * return true if page content is modified
     */
    public function replacePluginProperties(
        ilPageObject $a_page
    ): bool {
        if (!$this->hasExportablePlugins()) {
            return false;
        }

        $a_page->buildDom();
        $domdoc = $a_page->getDomDoc();
        $xpath = new DOMXPath($domdoc);
        $nodes = $xpath->query("//PageContent[child::Plugged]");

        $modified = false;

        /** @var DOMElement $pcnode */
        foreach ($nodes as $pcnode) {
            // page content id (unique in the page)
            $pc_id = $pcnode->getAttribute('PCID');
            $plnode = $pcnode->childNodes->item(0);
            $plugin_name = $plnode->getAttribute('PluginName');

            $id = $this->buildContentId($a_page->getParentType(), $a_page->getId(), $a_page->getLanguage(), $pc_id);

            $plugin_version = $this->getPCVersion($id);
            $properties = $this->getPCProperties($id);

            // update the version if modified by the plugin importer
            if ($plugin_version !== null) {
                $plnode->setAttribute('PluginVersion', $plugin_version);
                $modified = true;
            }

            // update the properties if modified by the plugin importer
            if (is_array($properties)) {
                /** @var DOMElement $child */
                foreach ($plnode->childNodes as $child) {
                    $plnode->removeChild($child);
                }
                foreach ($properties as $name => $value) {
                    $child = new DOMElement('PluggedProperty', (string) $value);
                    $plnode->appendChild($child);
                    $child->setAttribute('Name', $name);
                }
                $modified = true;
            }
        }

        return $modified;
    }
}
