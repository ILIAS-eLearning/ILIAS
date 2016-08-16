<?php

namespace ILIAS\UI\Component;

/**
 * Interface to be extended by components that have the possibility to bind
 * to Javascript.
 */
interface IsJSBindable {
	/**
 	 * Add some JS-code that binds to the given component on load.
	 *
	 * The closure gets the id of the component as string and is expected to return a string
	 * containing javascript statements.
	 *
	 * You should not assume anything about the id besides that it uniquely identifies the
	 * DOM-Element that represents the component you have bound to in HTML. This means: the
	 * id may change between different requests or on different pages. The id can not be
	 * known when the component is created and be generated at rendering time.
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
	 * @param	self
	 */
	public function withOnLoadCode(\Closure $binder);
} 
