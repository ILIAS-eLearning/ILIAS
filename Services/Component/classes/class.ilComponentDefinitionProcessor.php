<?php declare(strict_types=1);

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * An ilComponentDefinitionProcessor processes some attributes from a component.xml
 * (i.e. a service.xml or module.xml) and puts the information into the according
 * places in the provider of the processor.
 *
 * Make sure to understand that this is used in the context of processing xml via
 * sax-style parsing. That is, the processor needs to act in a stateful session
 * and react on beginning and ending of tags.
 */
interface ilComponentDefinitionProcessor
{
    /**
     * This methods is supposed to purge existing data in the provider of the
     * component, so new components can be added to a clean slate.
     */
    public function purge() : void;

    /**
     * This method is called when parsing of component.xml for the given component
     * starts.
     *
     * This is supposed to reset any internal parsing state.
     */
    public function beginComponent(string $component, string $type) : void;

    /**
     * This method is called when parsing of component.xml for the given component
     * ends.
     */
    public function endComponent(string $component, string $type) : void;

    /**
     * This is called when a tag starts in the context of the given component.
     *
     * @param string[] $attributes
     */
    public function beginTag(string $name, array $attributes) : void;

    /**
     * This is called when a tag ends in the context of the given component.
     */
    public function endTag(string $name) : void;
}
