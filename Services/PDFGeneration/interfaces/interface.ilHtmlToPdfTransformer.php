<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilHtmlToPdfTransformer
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilHtmlToPdfTransformer
{

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return bool
     */
    public static function supportMultiSourcesFiles();

    /**
     * @param string $a_path_to_file
     * @param string $a_target
     */
    public function createPDFFileFromHTMLFile($a_path_to_file, $a_target);

    /**
     * @param string $a_path_to_file
     * @param string $a_target
     */
    public function createPDFFileFromHTMLString($a_path_to_file, $a_target);

    /**
     * @return string
     */
    public function getPathToTestHTML();

    /**
     * @return bool
     */
    public function hasInfoInterface();
}
