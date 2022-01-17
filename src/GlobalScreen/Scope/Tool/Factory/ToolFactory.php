<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

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
 * Class ToolFactory
 * This factory provides you all available types for MainMenu GlobalScreen Tools.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ToolFactory
{
    
    /**
     * Returns you a Tool which can contain special features in s context
     * @param IdentificationInterface $identification
     * @return Tool
     * @see CalledContexts
     */
    public function tool(IdentificationInterface $identification) : Tool
    {
        return new Tool($identification);
    }
    
    /**
     * @param IdentificationInterface $identification
     * @return TreeTool
     */
    public function treeTool(IdentificationInterface $identification) : TreeTool
    {
        return new TreeTool($identification);
    }
}
