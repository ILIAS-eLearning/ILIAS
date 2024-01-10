<?php

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

declare(strict_types=1);

/***
 * This data abstracts a complete Scss file. A Scss file is composed of categories, variables and random comments
 * (unclassified information)
 */
class ilSystemStyleScssSettingsFile
{
    /**
     * List of items (variabe, category or comment) this file contains
     * @var ilSystemStyleScssItem[]
     */
    protected array $items = [];

    /**
     * Separated array with all variable ids (performance reasons)
     */
    protected array $variables_ids = [];

    /**
     * Separated array with all category ids (performance reasons)
     */
    protected array $categories_ids = [];

    /**
     * Complete path to the settings file
     */
    protected string $scss_variables_settings_path;

    protected string $file_name;

    public function __construct(string $scss_variables_settings_path, string $file_name)
    {
        $this->file_name = $file_name;
        $this->scss_variables_settings_path = $scss_variables_settings_path;
        $this->openFile($scss_variables_settings_path);
    }

    protected function openFile(string $scss_variables_settings_path): void
    {
        if (is_file($scss_variables_settings_path.'/'.$this->file_name)) {
            $this->readFile($scss_variables_settings_path);
        } else {
            throw new ilSystemStyleException(
                ilSystemStyleException::FILE_OPENING_FAILED,
                $scss_variables_settings_path
            );
        }
    }
    /**
     * Reads the file from the file system
     * @throws ilSystemStyleException
     */
    protected function readFile(string $scss_variables_settings_path): void
    {
        $last_variable_comment = '';
        $last_category_id = '';
        $last_category_name = '';

        $regex_category = '/\/\/==\s(.*)/'; //Matches //== Category Name
        $regex_category_by_line = '/^\/\/[\s]?$/'; //Matches // at the end of the line with not comment
        $regex_category_comment = '/\/\/##\s(.*)/'; //Matches Matches //## Category Description
        $regex_variable = '/^\$(.*)/'; //Matches @VariableName value;
        $regex_variable_comment = '/\/\/\*\*\s(.*)/'; //Matches //** Variable Comment
        $regex_variable_name = '/(?:\$)(.*?)(?:\:)/'; //Matches @variableName
        $regex_variable_value = '/(?::)(.*)(?:;)/'; //Matches value;
        $regex_variable_references = '/(?:\$)([a-zA-Z0-9_-]*)/'; //Matches references in value

        try {
            $handle = fopen($scss_variables_settings_path."/".$this->file_name, 'r');
        } catch (Exception $e) {
            throw new ilSystemStyleException(
                ilSystemStyleException::FILE_OPENING_FAILED,
                $scss_variables_settings_path
            );
        }

        if ($handle) {
            $line_number = 1;
            $last_line_is_category = false;
            //Reads file line by line
            while (($line = fgets($handle)) !== false) {
                //This might be part of the categories structure, if so, ignore
                if ($last_line_is_category && preg_match($regex_category_by_line, $line, $out)) {
                    $line = fgets($handle);
                }
                $last_line_is_category = false;
                if (preg_match($regex_category, $line, $out)) {
                    //Check Category
                    $last_category_id = $this->addItem(new ilSystemStyleScssCategory($out[1]));
                    $last_category_name = $out[1] ?: '';
                    $last_line_is_category = true;
                } elseif (preg_match($regex_category_comment, $line, $out)) {
                    //Check Comment Category
                    $last_category = $this->getItemById($last_category_id);
                    $last_category->setComment($out[1]);
                } elseif (preg_match($regex_variable_comment, $line, $out)) {
                    //Check Variables Comment
                    $last_variable_comment = $out[1];
                } elseif (preg_match($regex_variable, $line, $out)) {
                    //Check Variables
                    //Name
                    preg_match($regex_variable_name, $out[0], $variable);

                    //Value
                    preg_match($regex_variable_value, $line, $value);

                    //References
                    $temp_value = $value[0];
                    $references = [];
                    while (preg_match($regex_variable_references, $temp_value, $reference)) {
                        $references[] = $reference[1];
                        $temp_value = str_replace($reference, '', $temp_value);
                    }

                    //do not store the !default attribute in the variable
                    $value = str_replace(' !default', '', ltrim($value[1]));

                    $this->addItem(new ilSystemStyleScssVariable(
                        $variable[1],
                        $value,
                        $last_variable_comment,
                        $last_category_name,
                        $references
                    ));
                    $last_variable_comment = '';
                } else {
                    $this->addItem(new ilSystemStyleScssComment($line));
                }

                $line_number++;
            }
            fclose($handle);
        } else {
            throw new ilSystemStyleException(ilSystemStyleException::FILE_OPENING_FAILED);
        }
    }

    /**
     * Write the complete file back to the file system (including comments and random content)
     */
    public function write(string $scss_variables_settings_path = ""): void
    {
        if ($scss_variables_settings_path == "") {
            $path = $this->scss_variables_settings_path;
        } else {
            $path = $scss_variables_settings_path ;
        }
        file_put_contents($path . $this->file_name, $this->getContent());
    }

    public function getContent(): string
    {
        $output = '';

        foreach ($this->items as $item) {
            $output .= $item->__toString();
        }
        return $output;
    }

    public function addItem(ilSystemStyleScssItem $item): int
    {
        $id = array_push($this->items, $item) - 1;

        if (get_class($item) == ilSystemStyleScssCategory::class) {
            $this->categories_ids[] = $id;
        } elseif (get_class($item) == ilSystemStyleScssVariable::class) {
            $this->variables_ids[] = $id;
        }

        return $id;
    }

    /**
     * @return ilSystemStyleScssCategory[]
     */
    public function getCategories(): array
    {
        $categories = [];

        foreach ($this->categories_ids as $category_id) {
            $category = $this->items[$category_id];
            $categories[$category->getName()] = $category;
        }

        return $categories;
    }

    /**
     * @return ilSystemStyleScssCategory[]
     */
    public function getVariables(): array
    {
        $variables = [];

        foreach ($this->variables_ids as $variable_id) {
            $variable = $this->items[$variable_id];
            $variables[$variable->getName()] = $variable;
        }

        return $variables;
    }

    protected function getItemById(int $id): ilSystemStyleScssItem
    {
        return $this->items[$id];
    }

    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return string
     */
    public function getScssVariablesSettingsPath(): string
    {
        return $this->scss_variables_settings_path;
    }
}
