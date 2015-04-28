<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 *
 * This is an attempt to a PHP implementation of the idea of formlets [1].
 * General idea is to have an abstract and composable representation of forms, 
 * called Formlets, that can be transformed to a concrete Builder and 
 * Collector. 
 * While the Builder is responsible for creating an HTML representation of a 
 * Formlet, the Collector is responsible for collecting inputs of the user.
 *
 * The PHP implementations turns out to be a little more complex, since stuff 
 * like currying and static functions as values is not as handy as in static functional 
 * languages.
 *
 * [1] http://groups.inf.ed.ac.uk/links/papers/formlets-essence.pdf
 *     The Essence of Form Abstraction (Cooper, Lindley, wadler, Yallop)
 */

namespace Lechimp\Formlets;

use Lechimp\Formlets\Internal\Values as V;
use Lechimp\Formlets\Internal\Formlet as F;

/**
 * Consumer interface to Formlets library.
 */

class Formlets {
    /**
     * Construct a plain value from a PHP value. 
     *
     * @param   mixed   $value
     * @return  IValue
     */
    static function val($value) {
        return V::val($value); 
    }

    /** 
     * Construct a static function value from a closure or the name of an ordinary
     * static function. One could specify the arity to use php-static functions with optional
     * arguments. An array of arguments to be inserted in the first arguments 
     * of the static function could be passed optionally as well.
     * 
     * @param   Closure | string    $static function
     * @param   null | integer      $arity
     * @param   [mixed]             $args
     * @return  IValue 
     */
    static function fun($function, $arity = null, $args = null) {
        return V::fn($function, $arity, $args);
    }

    /**
     * Get the static function that applies the given value to another static function.
     *
     * @return  IValue
     */
    static function application_to(IValue $val) {
        return V::application_to($val);
    }

    /**
     * Get a static function that expects to other static functions and returns the composition
     * the two.
     *
     * @return  IValue 
     */
    static function composition() {
        return V::composition();
    }

    /**
     * Combine all given formlets to a new formlet left folding with $cmb;
     */
    static function formlet() {
        $formlets = func_get_args();
        if (count($formlets) == 0 || !($formlets[0] instanceof IFormlet)) {
            throw new Exception("Expected at least one formlet.");
        }
        $cur = $formlets[0];
        $amount = count($formlets);
        for ($i = 1; $i < $amount; ++$i) {
            $cur = $cur->cmb($formlets[$i]);
        }
        return $cur;
    }

    /**
     * Inject a value into a Formlet.
     *
     * @return IFormlet
     */
    static function inject(IValue $value) {
        return F::pure($value);
    }

    /**
     * Get a formlet that contains no value and represents a text.
     *
     * @param  string   $content
     * @return IFormlet
     */
    static function text($content) {
        return F::text($content);
    }

    /**
     * Get an input element that contains the inputted value.
     * 
     * @param  string                       $type   - HTML attribue
     * @param  [string => string] | null    $attrs  - HTML attributes
     * @return IFormlet
     */
    static function input($type, $attrs = null) {
        return F::input($type, $attrs);
    }

    /**
     * Get an input element for text.
     * 
     * @param   string | null               $default
     * @param   [string => string] | null   $attrs
     * @return IFormlet
     */
    static function text_input($default = null, $attrs = null) {
        return F::text_input($default, $attrs);
    }

    /**
     * Get an input element for a longer text.
     * 
     * @param   string | null               $default
     * @param   [string => string] | null   $attrs
     * @return IFormlet
     */
    static function textarea($default = null, $attrs = null) {
        return F::textarea($default, $attrs);
    }

    /**
     * Get a checkbox input element. 
     *
     * @param   bool                        $default
     * @param   [string => string] | null   $attrs
     * @return IFormlet
     */
    static function checkbox($default = false, $attrs = null) {
        return F::checkbox($default, $attrs);
    }

    /**
     * Get a submit button. Third parameter sets weather the submit button collects
     * a bool or not. Defaults to false.
     * 
     * @param   string                      $value 
     * @param   [string => string] | null   $attrs
     * @param   bool                        $collects
     * @return IFormlet
     */
    static function submit($value, $attrs = array(), $collects = false) {
        return F::submit($value, $attrs, $collects);
    }

    /**
     * Get a named button input.
     *
     * @param   string                      $value
     * @param   [string => string] | null   $attrs
     * @return IFormlet
     */
    static function button($value, $attrs = array()) {
        return F::button($value, $attrs);
    } 

    /**
     * Get an email input.
     *
     * @param   string | null               $default
     * @param   [string => string] | null   $attrs
     * @return IFormlet
     */
    static function email($default = null, $attrs = array()) {
        return F::email($default = null, $attrs);
    } 

    /**
     * Get an hidden input.
     *
     * @param   string                      $value
     * @param   [string => string] | null   $attrs
     * @return IFormlet
     */
    static function hidden($value, $attrs = array()) {
        return F::hidden($value, $attrs);
    } 

    /**
     * Get an number input. Performs server side validation as well. $min, $max and
     * $steps are used in the same manner as their responding HTML attributes. The 
     * three error paramaters determine which messages should be shown to the user
     * on errors in server side validation.
     *
     * @param   integer                     $value
     * @param   integer                     $min
     * @param   integer                     $max
     * @param   integer                     $step
     * @param   string                      $error_int
     * @param   string                      $error_range
     * @param   string                      $error_step
     * @param   [string => string] | null   $attrs
     * @return IFormlet
     */
    static function number( $value, $min, $max, $step, $attrs = array()
                   , $error_int = "No integer!"
                   , $error_range = "Not in range!"
                   , $error_step = "Steps mismatch!"
                   ) {
        return F::number( $value, $min, $max, $step, $attrs
                      , $error_int, $error_range, $error_step
                      );
    } 


    /**
     * Get a password input.
     *
     * @param   string                      $default
     * @param   [string => string] | null   $attrs
     * @return IFormlet
     */
    static function password($default = null, $attrs = array()) {
        return F::password($default, $attrs);
    } 

    /**
     * Get a named reset button.
     *
     * @param   string                      $value
     * @param   [string => string] | null   $attrs
     * @return IFormlet
     */
    static function reset_button($value, $attrs = array()) {
        return F::reset($value, $attrs);
    }

    /**
     * Get a search input.
     *
     * @param   string                      $default
     * @param   [string => string] | null   $attrs
     * @return IFormlet
     */
    static function search($default = null, $attrs = array()) {
        return F::search($default, $attrs);
    } 

    /**
     * Get a urlinput.
     *
     * @param   string                      $default
     * @param   [string => string] | null   $attrs
     * @return IFormlet
     */
    static function url($default = null, $attrs = array()) {
        return F::url($default, $attrs);
    } 

    /**
     * Get a select input.
     *
     * @param   [string]                    $options
     * @param   string                      $default
     * @param   [string => string] | null   $attrs
     * @return IFormlet
     */
    static function select($options, $default = null, $attrs = array()) {
        return F::select($options, $default, $attrs);
    }

    /**
     * Get one radio button input per option, wrapped in a span. If no attribute 
     * 'class' is given in $attrs, span will get class 'radiogroup'.
     *
     * @param   [string]                    $options
     * @param   string                      $default
     * @param   [string => string] | null   $attrs
     * @param   [string => string] | null   $attrs_opts
     * @return IFormlet
     */
    static function radio($options, $default = null, $attrs = array(), $attrs_opts = array()) {
        return F::radio($options, $default, $attrs, $attrs_opts);
    }

    /**
     * Wrap a formlet into a fieldset.
     * 
     * @param   string                      $legend
     * @param   IFormlet                    $formlet
     * @param   [string => string] | null   $attrs
     * @param   [string => string] | null   $legend_attrs
     * @return IFormlet
     */
    static function fieldset($legend, IFormlet $formlet
                     , $attrs = array(), $legend_attrs = array()) {
        return F::fieldset($legend, $formlet, $attrs, $legend_attrs);
    }

    /**
     * Put a label in front of an input formlet.
     *
     * @param   string                      $label
     * @param   IFormlet                    $formlet
     * @return IFormlet
     */
    static function with_label($label, IFormlet $formlet) {
        return F::with_label($label, $formlet);
    }

    /**
     * Append a span with class error to input formlet if the formlet collects 
     * errors.
     *
     * @param   IFormlet                    $formlet
     * @return IFormlet
     */
    static function with_errors(IFormlet $formlet) {
        return F::with_errors($formlet);
    }

    /**
     * Get a new form that processes a formlet. $id must be a unique id throughout
     * the program. $action is the action attribute for the form tag. $attrs are
     * more attributes for the form tag.
     *
     * @param   string                      $id
     * @param   string                      $action
     * @param   [string => string] | null   $attrs
     * @return  IForm
     */
    static function form($id, $action, IFormlet $formlet, $attrs = null) {
        return F::form($id, $action, $formlet, $attrs);
    }

    /**
     * Collect values in an array until stop is received.
     *
     * See example.php.
     *
     * @return IValue
     */
    static function collect() {
        return F::collect();
    }

    static function stop() {
        return V::val(new Stop());
    }
}
     
?>
