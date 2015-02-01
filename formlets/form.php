<?php

/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 + This program is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once("formlets.php");

class Form implements IForm {
    protected $_builder;// Builder 
    protected $_collector;// Collector 
    protected $_id;     // string
    protected $_input;  // [string => mixed] | null 
    protected $_result;  // mixed | null 

    public function _result() {
        return $this->_result;
    }

    public function __construct($id, $action, $attrs, IFormlet $formlet) {
        if (!preg_match("#[a-zA-Z][a-zA-Z0-9_]+#", $id)) {
            throw new Exception("Form::__construct: '$id' can not be used as "
                               ."id. Only use numbers and digits.");
        }
        guardIsFormlet($formlet);
        guardIsString($id);
        guardIsString($action);
        $attrs = defaultTo($attrs, array());
        guardEachAndKeys($attrs, "guardIsString", "guardIsString");
        $this->_id = $id;
        $this->_input = null;
        $this->_result = null;

        $attrs["method"] = "post";
        $attrs["action"] = $action;
        $formlet = $formlet
            ->mapHTML(_fn(function($dict, $html) use ($attrs) {
                return html_tag("form", $attrs, $html);
            }));
        
        $name_source = NameSource::instantiate($this->_id);
        $repr = $formlet->instantiate($name_source);
        $this->_builder = $repr["builder"];
        $this->_collector = $repr["collector"];
    }

    /**
     * Initializes the form. If no input array is given uses $_POST.
     * Return $this.
     *
     + @param   [string => mixed] | null    $input
     * @return  Form 
     */
    public function init($input = null) {
        if ($input === null) {
            $input = $_POST;
        }
        $this->_input = $input;
        $this->_result = null;
    }

    /**
     * Check whether form was submitted.
     *
     * @return  bool
     */
    public function wasSubmitted() {
        if ($this->_input === null) {
            return false;
        }

        if ($this->_result) {
            return true;
        }

        try {
            $this->_result = $this->_collector->collect($this->_input);
            return true;
        }
        catch (MissingInputError $e) {
            return false;
        }
    }

    /**
     * Check weather form was successfully evaluated.
     *
     * @return  bool
     */
    public function wasSuccessfull() {
        if (!$this->wasSubmitted()) {
            return false;
        }

        return !$this->_result->isError();
    }

    /**
     * Display form in its current state.
     *
     * @return  string
     */
    public function display() {
        if (!$this->wasSubmitted()) {
            return $this->_builder->build()->render();
        } 

        $render_dict = new RenderDict($this->_input, $this->_result);
        return $this->_builder->buildWithDict($render_dict)->render();
    }

    /**
     * Get the result of the form.
     * Throws if form was not submitted and successfully evaluated.
     * 
     * @return  mixed
     * @throws  Exception
     */ 
    public function result() {
        if ( !$this->wasSubmitted()
        ||   !$this->wasSuccessfull()) {
            throw new Exception("Form::result: Form was not submitted successfully.");
        }
        if ( $this->_result->isApplicable()) {
            throw new Exception("Form::result: Result is no value but a function."); 
        }

        return $this->_result->get(); 
    }

    /**
     * Get an error as string.
     * Throws if form was submitted successfully.
     * 
     * @return string
     * @throws Exception
     */
    public function error() {
        if ( $this->wasSuccessfull()) {
            throw new Exception("Form::error: Form was submitted successfully.");
        }

        return $this->_result->error();
    }
}

/**
 * Get a new form that processes a formlet. $id must be a unique id throughout
 * the program. $action is the action attribute for the form tag. $attrs are
 * more attributes for the form tag.
 *
 * @param  string                       $id
 * @param  string                       $action
 * @param   [string => string] | null   $attrs
 * @return Form
 */
function _form($id, $action, IFormlet $formlet, $attrs = null) {
    return new Form($id, $action, $attrs, $formlet);
}

?>
