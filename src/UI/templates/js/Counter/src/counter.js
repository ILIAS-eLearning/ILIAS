import {counterFactory} from "./counter.main.js";

il.UI = il.UI || {};
/**
 * Declaration and implementation of the il.UI.counter scope.
 *
 * Note that this scope provides a public interface through which counters can
 * be accessed and manipulated by the client side.
 *
 * This scope contains only the getCounterObject through which a counter object can
 * be accessed.
 *
 * See the public_object_interace for a list of functions of this object offered
 * to the public.
 *
 * Example Usage:
 *
 * //Step 1: Get the counter Object
 * var counter = il.UI.counter.getCounterObject($some_jquery_object);
 *
 * //Step 2: Do stuff with the counter Object
 * var novelty_count = counter.setNoveltyCount(3).getNoveltyCount(); //novelty count should be 3
 * novelty_count = counter.setNoveltyToStatus().getNoveltyCount(); //novelty count should be 0, status count 3
 */
il.UI.counter = counterFactory($);

