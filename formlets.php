<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This is an attempt to a PHP implementation of the idea of formlets [1].
 * General idea is to have an abstract and composable representation of forms, 
 * called Formlets, that can be transformed to a concrete Builder and 
 * Collector. 
 * While the Builder is responsible for creating an HTML representation of a 
 * Formlet, the Collector is responsible for collecting inputs of the user.
 *
 * The PHP implementations turns out to be a little more complex, since stuff 
 * like currying and functions as values is not as handy as in functional 
 * languages.
 *
 * [1] http://groups.inf.ed.ac.uk/links/papers/formlets-essence.pdf
 *     The Essence of Form Abstraction (Cooper, Lindley, wadler, Yallop)
 */

/**
 * Consumer interface to Formlets library.
 */

/**
 * Interface to the Value representation used for the Formlets.
 *
 * It's a closed union over three types of values that is ordinary values, 
 * function values and error values.
 */
interface IValue {
    /**
     * The origin of a value is the location in the 'real' world, where the
     * value originates from.
     *
     * @return  string | null
     */
    public function origin();

    /**
     * Get the PHP value out of this.
     *
     * Throws when value is an error or a function.
     *
     * @return  mixed
     * @throws  GetError
     */
    public function get();

    /**
     * Apply the value to another value.
     *
     * Throws when value is an ordinary value.
     *
     * @return  IValue
     * @throws  ApplyError
     */
    public function apply(IValue $to);

    /**
     * Return a new function that catches Exceptions and returns them as error
     * values. Returns null when value is an ordinary value.
     *
     * @param   string          $exc_class
     * @return  IValue | null
     * @throws
     */
    public function catchAndReify($exc_class);

    /**
     * Returns string with error message when value is error and
     * null when it's not.
     *
     * @return  string | null
     */
    public function error();
} 

require_once("formlets/values.php");

/**
 * Construct a plain value from a PHP value. 
 *
 * @param   mixed   $value
 * @return  IValue
 */
function val($value) {
    return _val($value); 
}

/** 
 * Construct a function value from a closure or the name of an ordinary
 * function. One could specify the arity to use php-functions with optional
 * arguments. An array of arguments to be inserted in the first arguments 
 * of the function could be passed optionally as well.
 * 
 * @param   Closure | string    $function
 * @param   null | integer      $arity
 * @param   [mixed]             $args
 * @return  IValue 
 */
function fun($function, $arity = null, $args = null) {
    return _fn($function, $arity, $args);
}

/**
 * Get the function that applies the given value to another function.
 *
 * @return  IValue
 */
function application_to(IValue $val) {
    return _application_to($val);
}

/**
 * Get a function that expects to other functions and returns the composition
 * the two.
 *
 * @return  IValue 
 */
function composition() {
    return _composition();
}


/**
 * A formlet represents one part of a form. It can be combined with other formlets
 * to yield new formlets. Formlets are immutable, that is they can be reused in
 * as many places as you like. All methods return fresh Formlets instead of muting
 * the Formlets they are called upon.
 */
interface IFormlet {
    /**
     * Combined the formlet with another formlet and get a new formlet. Will apply 
     * a function value in this formlet to any value in the other formlet.
     *
     * @return  IFormlet
     */
    public function cmb(IFormlet $formlet);

    /**
     * Get a new formlet with an additional check of a predicate on the input to 
     * the formlet and an error message for the case the predicate fails. The 
     * predicates has to be a function from mixed to bool.
     * 
     * @param   IValue  $predicate
     * @param   string  $error
     * @return  IFormlet
     */
    public function satisfies(IValue $predicate, $error);

    /**
     * Map a function over the input value.
     *
     * @return IFormlet 
     */
    public function map(IValue $transformation);
}

require_once("formlets/formlets.php");

/**
 * Combine all given formlets to a new formlet left folding with $cmb;
 */
function formlet() {
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
function inject(IValue $value) {
    return _pure($value);
}

/**
 * Get a formlet that contains no value and represents a text.
 *
 * @param  string   $content
 * @return IFormlet
 */
function text($content) {
    return _text($content);
}

/**
 * Get an input element that contains the inputted value.
 * 
 * @param  string                       $type   - HTML attribue
 * @param  [string => string] | null    $attrs  - HTML attributes
 * @return IFormlet
 */
function input($type, $attrs = null) {
    return _input($type, $attrs);
}

/**
 * Get an input element for text.
 * 
 * @param   string | null               $default
 * @param   [string => string] | null   $attrs
 * @return IFormlet
 */
function text_input($default = null, $attrs = null) {
    return _text_input($default, $attrs);
}

/**
 * Get an input element for a longer text.
 * 
 * @param   string | null               $default
 * @param   [string => string] | null   $attrs
 * @return IFormlet
 */
function textarea($default = null, $attrs = null) {
    return _textarea($default, $attrs);
}

/**
 * Get a checkbox input element. 
 *
 * @param   bool                        $default
 * @param   [string => string] | null   $attrs
 * @return IFormlet
 */
function checkbox($default = false, $attrs = null) {
    return _checkbox($default, $attrs);
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
function submit($value, $attrs = array(), $collects = false) {
    return _submit($value, $attrs, $collects);
}

/**
 * Get a named button input.
 *
 * @param   string                      $value
 * @param   [string => string] | null   $attrs
 * @return IFormlet
 */
function button($value, $attrs = array()) {
    return _button($value, $attrs);
} 

/**
 * Get an email input.
 *
 * @param   string | null               $default
 * @param   [string => string] | null   $attrs
 * @return IFormlet
 */
function email($default = null, $attrs = array()) {
    return _email($default = null, $attrs);
} 

/**
 * Get an hidden input.
 *
 * @param   string                      $value
 * @param   [string => string] | null   $attrs
 * @return IFormlet
 */
function hidden($value, $attrs = array()) {
    return _hidden($value, $attrs);
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
function number( $value, $min, $max, $step, $attrs = array()
               , $error_int = "No integer!"
               , $error_range = "Not in range!"
               , $error_step = "Steps mismatch!"
               ) {
    return _number( $value, $min, $max, $step, $attrs
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
function password($default = null, $attrs = array()) {
    return _password($default, $attrs);
} 

/**
 * Get a named reset button.
 *
 * @param   string                      $value
 * @param   [string => string] | null   $attrs
 * @return IFormlet
 */
function reset_button($value, $attrs = array()) {
    return _reset($value, $attrs);
}

/**
 * Get a search input.
 *
 * @param   string                      $default
 * @param   [string => string] | null   $attrs
 * @return IFormlet
 */
function search($default = null, $attrs = array()) {
    return _search($default, $attrs);
} 

/**
 * Get a urlinput.
 *
 * @param   string                      $default
 * @param   [string => string] | null   $attrs
 * @return IFormlet
 */
function url($default = null, $attrs = array()) {
    return _url($default, $attrs);
} 

/**
 * Get a select input.
 *
 * @param   [string]                    $options
 * @param   string                      $default
 * @param   [string => string] | null   $attrs
 * @return IFormlet
 */
function select($options, $default = null, $attrs = array()) {
    return _select($options, $default, $attrs);
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
function radio($options, $default = null, $attrs = array(), $attrs_opts = array()) {
    return _radio($options, $default, $attrs, $attrs_opts);
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
function fieldset($legend, IFormlet $formlet
                 , $attrs = array(), $legend_attrs = array()) {
    return _fieldset($legend, $formlet, $attrs, $legend_attrs);
}

/**
 * Put a label in front of an input formlet.
 *
 * @param   string                      $label
 * @param   IFormlet                    $formlet
 * @return IFormlet
 */
function with_label($label, IFormlet $formlet) {
    return _with_label($label, $formlet);
}

/**
 * Append a span with class error to input formlet if the formlet collects 
 * errors.
 *
 * @param   IFormlet                    $formlet
 * @return IFormlet
 */
function with_errors(IFormlet $formlet) {
    return _with_errors($formlet);
}


/** 
 * A form turns a formlet in a representation that could be processed to display
 * a html page and retreive input.
 */
interface IForm {
   /**
     * Initializes the form. If no input array is given uses $_POST.
     * Return $this.
     *
     * @param   [string => mixed] | null    $input
     * @return  IForm 
     */
    public function init($input = null);

    /**
     * Check whether form was submitted.
     *
     * @return  bool
     */
    public function wasSubmitted();

    /**
     * Check weather form was successfully evaluated.
     *
     * @return  bool
     */
    public function wasSuccessfull();

    /**
     * Get a HTML-string of the form in its current state.
     *
     * @return  string
     */
    public function html();

    /**
     * Get the result of the form.
     * Throws if form was not submitted and successfully evaluated.
     * 
     * @return  mixed
     * @throws  Exception
     */ 
    public function result();

    /**
     * Get an error as string.
     * Throws if form was submitted successfully.
     * 
     * @return string
     * @throws Exception
     */
    public function error();
}

require_once("formlets/form.php");

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
function form($id, $action, IFormlet $formlet, $attrs = null) {
    return _form($id, $action, $formlet, $attrs);
}


require_once("formlets/lib.php");

/**
 * Collect values in an array until stop is received.
 *
 * See example.php.
 *
 * @return IValue
 */
function collect() {
    return _collect();
}

function stop() {
    return val(new Stop());
}
 
?>
