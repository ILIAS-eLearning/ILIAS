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

    public function createPDFFileFromHTMLFile(string $a_path_to_file, string $a_target);

    public function createPDFFileFromHTMLString(string $a_path_to_file, string $a_target);

    /**
     * @return string
     */
    public function getPathToTestHTML();

    /**
     * @return bool
     */
    public function hasInfoInterface();
}
