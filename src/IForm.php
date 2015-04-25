<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

//namespace Lechimp\Formlets;

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

?>
