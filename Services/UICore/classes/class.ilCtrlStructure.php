<?php
/* Copyright (c) 2020 Richard Klees, Extended GPL, see docs/LICENSE */

/**
 * The CtrlStructure knows how GUIs call each other and where the code of the
 * guis is located.
 */
class ilCtrlStructure
{
    /**
     * @var array<string, string>
     */
    protected $class_scripts;

    /**
     * @var array<string, string[]>
     */
    protected $class_children;

    public function __construct(array $class_scripts = [], array $class_children = [])
    {
        $this->class_scripts = [];
        $this->class_children = [];
        foreach ($class_scripts as $k => $v) {
            $this->addClassScript($k, $v);
        }
        foreach ($class_children as $k => $vs) {
            foreach ($vs as $v) {
                $this->addClassChild($k, $v);
            }
        }
    }

    public function withClassScript(string $class, string $file_path) : \ilCtrlStructure
    {
        $clone = clone $this;
        $clone->addClassScript($class, $file_path);
        return $clone;
    }

    public function withClassChild(string $parent, string $child) : \ilCtrlStructure
    {
        $clone = clone $this;
        $clone->addClassChild($parent, $child);
        return $clone;
    }

    public function getClassScripts() : \Generator
    {
        foreach ($this->class_scripts as $k => $v) {
            yield $k => $v;
        }
    }

    public function getClassChildren() : \Generator
    {
        foreach ($this->class_children as $k => $v) {
            yield $k => $v;
        }
    }

    public function getClassScriptOf(string $class) : ?string
    {
        if (!isset($this->class_scripts[$class])) {
            return null;
        }
        return $this->class_scripts[$class];
    }

    protected function addClassScript(string $class, string $file_path) : void
    {
        if ($class == "") {
            throw new \InvalidArgumentException(
                "Can't add class script for an empty class."
            );
        }

        if (isset($this->class_scripts[$class]) && $this->class_scripts[$class] != $file_path) {
            $e = new \RuntimeException(
                "Can't add script '$file_path' for class '$class', a script for that class already exists."
            );
            $e->file_path = $file_path;
            $e->class = $class;
            throw $e;
        }

        $this->class_scripts[$class] = $file_path;
    }

    protected function addClassChild(string $parent, string $child) : void
    {
        if ($parent == "") {
            throw new \InvalidArgumentException(
                "Can't add class child for an empty parent."
            );
        }

        if (!isset($this->class_children[$parent])) {
            $this->class_children[$parent] = [];
        }

        if (!in_array($child, $this->class_children[$parent])) {
            $this->class_children[$parent][] = $child;
        }
    }
}
