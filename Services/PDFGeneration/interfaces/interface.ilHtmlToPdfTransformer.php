<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Interface ilHtmlToPdfTransformer
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilHtmlToPdfTransformer
{
    public function getId() : string;

    public function getTitle() : string;

    public function isActive() : bool;

    public static function supportMultiSourcesFiles() : bool;

    public function createPDFFileFromHTMLFile(string $a_path_to_file, string $a_target);

    public function createPDFFileFromHTMLString(string $a_path_to_file, string $a_target);

    public function getPathToTestHTML() : string;

    public function hasInfoInterface() : bool;
}
