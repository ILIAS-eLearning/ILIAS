<?php

/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 */

require_once("formlets.php");

class Form implements IForm {
    protected $_builder;// Builder 
    protected $_collector;// Collector 
    protected $_id;     // string
    protected $_input;  // [string => mixed] | null 
    protected $_result;  // mixed | null 

    public function __construct($id, IFormlet $formlet) {
        if (!preg_match("#[a-zA-Z][a-zA-Z0-9]+#", $id)) {
            throw new Exception("Form::__construct: '$id' can not be used as "
                               ."id. Only use numbers and digits.");
        }
        guardIsFormlet($formlet);
        guardIsString($id);
        $this->_id = $id;
        $this->_input = null;
        $this->_result = null;
        
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
        catch (MissingInputError as $e) {
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

        return $this->_result->isError();
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
        &&   !$this->wasSuccessfull())
            throw new Exception("Form::result: Form was not submitted successfully.");
        }
        if ( $this->_result->isApplicable())
            throw new Exception("Form::result: Result is no value but a function."); 
        }

        return $this->_result->get(); 
    }
}

/**
 * Get a new form that processes a formlet.
 *
 * @param  string   $id     - must be unique throughout the program.
 * @return Form
 */
function _form($id, IFormlet $formlet) {
    return new Form($id, $formlet);
}

?>
