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
 * This data abstracts a complete less file. A less file is composed of categories, variables and random comments
 * (unclassified information)
 */
class ilSystemStyleLessFile
{
    /**
     * List of items (variabe, category or comment) this file contains
     * @var ilSystemStyleLessItem[]
     */
    protected array $items = [];

    /**
     * Separated array with all comments ids (performance reasons)
     */
    protected array $comments_ids = [];

    /**
     * Separated array with all variable ids (performance reasons)
     */
    protected array $variables_ids = [];

    /**
     * Separated array with all category ids (performance reasons)
     */
    protected array $categories_ids = [];

    /**
     * Complete path the the variables file on the file system
     */
    protected string $less_variables_file_path_name = '';

    public function __construct(string $less_variables_file_path_name)
    {
        $this->less_variables_file_path_name = $less_variables_file_path_name;
        $this->read();
    }

    /**
     * Reads the file from the file system
     * @throws ilSystemStyleException
     */
    public function read(): void
    {
        $last_variable_comment = '';
        $last_category_id = '';
        $last_category_name = '';

        $regex_category = '/\/\/==\s(.*)/'; //Matches //== Category Name
        $regex_category_by_line = '/^\/\/[\s]?$/'; //Matches // at the end of the line with not comment
        $regex_category_comment = '/\/\/##\s(.*)/'; //Matches Matches //## Category Description
        $regex_variable = '/^@(.*)/'; //Matches @VariableName value;
        $regex_variable_comment = '/\/\/\*\*\s(.*)/'; //Matches //** Variable Comment
        $regex_variable_name = '/(?:@)(.*)(?:\:)/'; //Matches @variableName
        $regex_variable_value = '/(?::)(.*)(?:;)/'; //Matches value;
        $regex_variable_references = '/(?:@)([a-zA-Z0-9_-]*)/'; //Matches references in value

        try {
            $handle = fopen($this->getLessVariablesFilePathName(), 'r');
        } catch (Exception $e) {
            throw new ilSystemStyleException(
                ilSystemStyleException::FILE_OPENING_FAILED,
                $this->getLessVariablesFilePathName()
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
                    $last_category_id = $this->addItem(new ilSystemStyleLessCategory($out[1]));
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

                    $this->addItem(new ilSystemStyleLessVariable(
                        $variable[1],
                        ltrim($value[1]),
                        $last_variable_comment,
                        $last_category_name,
                        $references
                    ));
                    $last_variable_comment = '';
                } else {
                    $this->addItem(new ilSystemStyleLessComment($line));
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
    public function write(): void
    {
        file_put_contents($this->getLessVariablesFilePathName(), $this->getContent());
    }

    public function getContent(): string
    {
        $output = '';

        foreach ($this->items as $item) {
            $output .= $item->__toString();
        }
        return $output;
    }

    public function addItem(ilSystemStyleLessItem $item): int
    {
        $id = array_push($this->items, $item) - 1;

        if (get_class($item) == 'ilSystemStyleLessComment') {
            $this->comments_ids[] = $id;
        } elseif (get_class($item) == 'ilSystemStyleLessCategory') {
            $this->categories_ids[] = $id;
        } elseif (get_class($item) == 'ilSystemStyleLessVariable') {
            $this->variables_ids[] = $id;
        }

        return $id;
    }

    /**
     * @return ilSystemStyleLessCategory[]
     */
    public function getCategories(): array
    {
        $categories = [];

        foreach ($this->categories_ids as $category_id) {
            $categories[] = $this->items[$category_id];
        }

        return $categories;
    }

    /**
     * @return ilSystemStyleLessVariable[]
     */
    public function getVariablesPerCategory(string $category = ''): array
    {
        $variables = [];

        foreach ($this->variables_ids as $variables_id) {
            if (!$category || $this->items[$variables_id]->getCategoryName() == $category) {
                $variables[] = $this->items[$variables_id];
            }
        }

        return $variables;
    }

    public function getItemById(int $id): ilSystemStyleLessItem
    {
        return $this->items[$id];
    }

    public function getVariableByName(string $name = ''): ?ilSystemStyleLessItem
    {
        foreach ($this->variables_ids as $variables_id) {
            if ($this->items[$variables_id]->getName() == $name) {
                return $this->items[$variables_id];
            }
        }
        return null;
    }

    public function getReferencesToVariable(string $variable_name): array
    {
        $references = [];

        foreach ($this->variables_ids as $id) {
            foreach ($this->items[$id]->getReferences() as $reference) {
                if ($variable_name == $reference) {
                    $references[] = $this->items[$id]->getName();
                }
            }
        }
        return $references;
    }

    public function getReferencesToVariableAsString(string $variable_name): string
    {
        $references_string = '';
        foreach ($this->getReferencesToVariable($variable_name) as $reference) {
            $references_string .= "$reference; ";
        }
        return $references_string;
    }

    public function getRefAndCommentAsString(string $variable_name, string $refs_wording): string
    {
        $references_string = '';
        foreach ($this->getReferencesToVariable($variable_name) as $reference) {
            $references_string .= "$reference; ";
        }

        $variable = $this->getVariableByName($variable_name);

        if ($references_string != '') {
            if ($variable->getComment()) {
                $info = $variable->getComment() . '</br>' . $refs_wording . ' ' . $references_string;
            } else {
                $info = $refs_wording . ' ' . $references_string;
            }
        } else {
            $info = $variable->getComment();
        }

        return $info;
    }

    public function getLessVariablesFilePathName(): string
    {
        return $this->less_variables_file_path_name;
    }

    public function setLessVariablesFilePathName(string $less_variables_file_path_name): void
    {
        $this->less_variables_file_path_name = $less_variables_file_path_name;
    }

    /**
     * @return ilSystemStyleLessVariable[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getCommentsIds(): array
    {
        return $this->comments_ids;
    }

    public function getVariablesIds(): array
    {
        return $this->variables_ids;
    }

    public function getCategoriesIds(): array
    {
        return $this->categories_ids;
    }
}
