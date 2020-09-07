<?php

interface ilRendererConfig
{

    /**
     * @param \ilPropertyFormGUI $form    The config form for the administration
     * @param string             $service Service Title
     * @param string             $purpose Purpose Title
     *
     * @return \ilPropertyFormGUI The config form
     */
    public function addConfigElementsToForm(\ilPropertyFormGUI $form, $service, $purpose);

    /**
     * @param \ilPropertyFormGUI $form    The config form for the administration
     * @param string             $service Service Title
     * @param string             $purpose Purpose Title
     * @param array              $config  KV-array with config
     *
     * @return \ilPropertyFormGUI The config form
     */
    public function populateConfigElementsInForm(\ilPropertyFormGUI $form, $service, $purpose, $config);

    /**
     * @param \ilPropertyFormGUI $form    The config form for the administration
     * @param string             $service Service Title
     * @param string             $purpose Purpose Title
     *
     * @return boolean True, if the form holds a valid config
     */
    public function validateConfigInForm(\ilPropertyFormGUI $form, $service, $purpose);

    /**
     * @param \ilPropertyFormGUI $form    The config form for the administration
     * @param string             $service Service Title
     * @param string             $purpose Purpose Title
     *
     * @return array KV-array with config
     */
    public function getConfigFromForm(\ilPropertyFormGUI $form, $service, $purpose);

    /**
     * @param string $service Service Title
     * @param string $purpose Purpose Title
     *
     * @return array KV-array with config
     */
    public function getDefaultConfig($service, $purpose);
}
