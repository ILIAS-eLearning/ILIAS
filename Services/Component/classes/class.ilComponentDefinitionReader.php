<?php declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilComponentDefinitionReader
{
    /**
     * @var ilComponentDefinitionProcessor[]
     */
    protected array $processors;

    public function __construct(
        ilComponentDefinitionProcessor ...$processor
    ) {
        $this->processors = $processor;
    }

    /**
     * This methods is supposed to purge existing data in the registered
     * processor.
     */
    public function purge() : void
    {
        foreach ($this->processors as $p) {
            $p->purge();
        }
    }

    /**
     * This reads the component.xml of all components in the core and processes
     * them with the provided processor.
     */
    public function readComponentDefinitions() : void
    {
        foreach ($this->getComponents() as [$type, $component, $path]) {
            $file = $this->readFile($path);
            foreach ($this->processors as $processor) {
                $processor->beginComponent($component, $type);
            }
            $this->parseComponentXML($type, $component, $file);
            foreach ($this->processors as $processor) {
                $processor->endComponent($component, $type);
            }
        }
    }

    protected function readFile(string $path) : string
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(
                "Cannot find file $path."
            );
        }
        return file_get_contents($path);
    }

    protected function parseComponentXML(string $type, string $component, string $xml) : void
    {
        $xml_parser = null;
        try {
            $xml_parser = xml_parser_create("UTF-8");
            xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
            xml_set_object($xml_parser, $this);
            xml_set_element_handler($xml_parser, 'beginTag', 'endTag');
            if (!xml_parse($xml_parser, $xml)) {
                $code = xml_get_error_code($xml_parser);
                $line = xml_get_current_line_number($xml_parser);
                $col = xml_get_current_column_number($xml_parser);
                $msg = xml_error_string($code);
                throw new \InvalidArgumentException(
                    "Error $code component xml of $type/$component, on line $line in column $col: $msg"
                );
            }
        } finally {
            if ($xml_parser) {
                xml_parser_free($xml_parser);
            }
        }
    }

    public function beginTag($_, string $name, array $attributes) : void
    {
        foreach ($this->processors as $processor) {
            $processor->beginTag($name, $attributes);
        }
    }

    public function endTag($_, string $name) : void
    {
        foreach ($this->processors as $processor) {
            $processor->endTag($name);
        }
    }

    /**
     * @return Iterator<string>
     */
    protected function getComponents() : \Iterator
    {
        foreach ($this->getComponentInfo("Modules", "module.xml") as $i) {
            yield $i;
        }
        foreach ($this->getComponentInfo("Services", "service.xml") as $i) {
            yield $i;
        }
    }

    /**
     * @return Iterator<array>
     */
    protected function getComponentInfo(string $type, string $name) : \Iterator
    {
        $dir = __DIR__ . "/../../../" . $type;
        foreach ($this->getComponentPaths($dir, $name) as $c) {
            yield [
                $type,
                $c,
                realpath($dir . "/" . $c . "/" . $name)
            ];
        }
    }

    /**
     * @return Iterator<string>
     */
    protected function getComponentPaths(string $root, string $name) : \Iterator
    {
        $dir = opendir($root);
        while ($sub = readdir($dir)) {
            if ($sub === "." || $sub === "..") {
                continue;
            }
            if (!is_dir($root . "/" . $sub)) {
                continue;
            }
            if (!is_file($root . "/" . $sub . "/" . $name)) {
                continue;
            }
            yield $sub;
        }
    }
}
