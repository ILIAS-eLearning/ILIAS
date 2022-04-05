<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
interface ilRendererConfig
{

    /**
     * @param \ilPropertyFormGUI $form    The config form for the administration
     * @param string             $service Service Title
     * @param string             $purpose Purpose Title
     *
     * @return \ilPropertyFormGUI The config form
     */
    public function addConfigElementsToForm(\ilPropertyFormGUI $form, string $service, string $purpose);

    /**
     * @param \ilPropertyFormGUI $form    The config form for the administration
     * @param string             $service Service Title
     * @param string             $purpose Purpose Title
     * @param array              $config  KV-array with config
     *
     * @return \ilPropertyFormGUI The config form
     */
    public function populateConfigElementsInForm(\ilPropertyFormGUI $form, string $service, string $purpose, array $config);

    /**
     * @param \ilPropertyFormGUI $form    The config form for the administration
     * @param string             $service Service Title
     * @param string             $purpose Purpose Title
     *
     * @return boolean True, if the form holds a valid config
     */
    public function validateConfigInForm(\ilPropertyFormGUI $form, string $service, string $purpose);

    /**
     * @param \ilPropertyFormGUI $form    The config form for the administration
     * @param string             $service Service Title
     * @param string             $purpose Purpose Title
     *
     * @return array KV-array with config
     */
    public function getConfigFromForm(\ilPropertyFormGUI $form, string $service, string $purpose);

    /**
     * @param string $service Service Title
     * @param string $purpose Purpose Title
     *
     * @return array KV-array with config
     */
    public function getDefaultConfig(string $service, string $purpose);
}
