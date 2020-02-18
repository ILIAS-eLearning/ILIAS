<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component;

/**
 * Interface to be extended by components that have the possibility to bind
 * to Javascript.
 */
interface JavaScriptBindable
{
    /**
     * Add some JS-code that binds to the given component on load.
     *
     * The closure gets the id of the component as string and is expected to return a string
     * containing javascript statements.
     *
     * You should not assume anything about the id besides that it uniquely identifies the
     * DOM-Element that represents the component you have bound to in HTML. This means: the
     * id may change between different requests or on different pages. The id can not be
     * known when the component is created and will be generated at rendering time.
     *
     * This binding to an id can not be achieved via an getId-method. The id of an HTML
     * element can not be known at creation time of the component, since we don't know how
     * often a component will be rendered there. Thus, a truely unique id can only be
     * created at rendering time. To still give full flexibility to the user of a component,
     * we use a closure to create the binding code. A strictly less powerfull alternative
     * would have been to pass some string with a known placeholder for the id.
     *
     * ATTENTION: This is the method of choice to bind JS to UI-components at this point in
     * the development of the UI-Framework. It most probably will be replaced by a more
     * powerful abstraction.
     *
     * @example:
     * 		$some_component->withOnLoadCode(function($id) {
     *			return "alert('Component has id: $id');";
     *		});
     *
     * @param	\Closure	$binder
     * @return	self
     */
    public function withOnLoadCode(\Closure $binder);

    /**
     * Add some onload-code to the component instead of replacing the existing one.
     *
     * Must work like getOnLoadCode was called and the result of the existing binder
     * (if any) and the result of the new binder are concatenated in a new binder.
     *
     * @param	\Closure	$binder
     * @return	self
     */
    public function withAdditionalOnLoadCode(\Closure $binder);

    /**
     * Get the currently bound on load code.
     *
     * @return	\Closure|null
     */
    public function getOnLoadCode();
}
