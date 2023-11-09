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
class ilSystemStyleScssSettings
{
    /**
     * @var ilSystemStyleScssSettingsFile[]
     */
    protected array $files = [];

    /**
     * @var ilSystemStyleScssVariable[]
     */
    protected array $variables = [];

    /**
     * @var ilSystemStyleScssCategory[]
     */
    protected array $categories = [];

    /**
     * Complete path the the settings folder on the file system
     */
    protected string $scss_variables_settings_path = '';

    public function __construct(string $scss_variables_settings_path)
    {
        $this->scss_variables_settings_path = $scss_variables_settings_path;
        $this->readFolder();
    }

    public function readFolder(): void
    {
        if (is_dir($this->scss_variables_settings_path)) {
            $files = scandir($this->scss_variables_settings_path, SCANDIR_SORT_ASCENDING);
            foreach ($files as $file) {
                $file_path = $this->scss_variables_settings_path . '/' . $file;
                if ($file != "." && $file != ".." && !is_dir($file_path)) {
                    $this->files[] = new ilSystemStyleScssSettingsFile($this->scss_variables_settings_path, $file);
                }
            }
        } else {
            throw new ilSystemStyleException(
                ilSystemStyleException::FOLDER_OPENING_FAILED,
                $this->scss_variables_settings_path
            );
        }
    }

    public function readAndreplaceContentOfFolder(array $replacements): void
    {
        if (is_dir($this->scss_variables_settings_path)) {
            $files = scandir($this->scss_variables_settings_path, SCANDIR_SORT_ASCENDING);
            foreach ($files as $file) {
                $file_path = $this->scss_variables_settings_path . '/' . $file;
                if ($file != "." && $file != ".." && !is_dir($file_path)) {
                    $content = file_get_contents($file_path);
                    foreach ($replacements as $search => $replace) {
                        $content = str_replace($search, $replace, $content);
                    }
                    file_put_contents($file_path, $content);
                }
            }
        }
    }

    /**
     * Write the complete files back to the file system (including comments and random content)
     */
    public function write(string $new_path = ""): void
    {
        if ($new_path == "") {
            $path = $this->scss_variables_settings_path;
        } else {
            $path = $new_path ;
        }
        foreach ($this->files as $file) {
            $file->write($path);
        }
    }

    public function getContent(): string
    {
        $output = '';

        foreach ($this->files as $file) {
            $output .= $file->getContent();
        }
        return $output;
    }

    /**
     * @return ilSystemStyleScssCategory[]
     */
    public function getCategories(): array
    {
        if (count($this->categories) == 0) {
            foreach ($this->files as $file) {
                $this->categories = array_merge($this->categories, $file->getCategories());
            }
        }
        return $this->categories;
    }

    public function getCategoryByName(string $name): ilSystemStyleScssCategory
    {
        $categories = $this->getCategories();
        return $categories[$name];
    }

    /**
     * @return ilSystemStyleScssVariable[]
     */
    public function getVariables(): array
    {
        if (count($this->variables) == 0) {
            foreach ($this->files as $file) {
                $this->variables = array_merge($this->variables, $file->getVariables());
            }
        }
        return $this->variables;
    }

    public function getVariableByName(string $name): ilSystemStyleScssVariable
    {
        $categories = $this->getVariables();
        return $categories[$name];
    }

    /**
     * @return ilSystemStyleScssVariable[]
     */
    public function getVariablesPerCategory(string $category_name = ''): array
    {
        $variables = [];
        foreach ($this->getVariables() as $variable) {
            if ($variable->getCategoryName() == $category_name) {
                $variables[] = $variable;
            }
        }

        return $variables;
    }

    public function getReferencesToVariable(string $variable_name): array
    {
        $references = [];

        foreach ($this->getVariables() as $variable) {
            foreach ($variable->getReferences() as $reference) {
                if ($variable_name == $reference) {
                    $references[] = $variable->getName();
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

    public function getVariablesForDelosOverride(): string
    {
        $out = "";
        foreach ($this->getVariables() as $variable) {
            $out .= $variable->getForDelosOverride();
        }
        return $out;
    }

    /**
     * @return ilSystemStyleScssItem[]
     */
    public function getItems(): array
    {
        $item = [];
        foreach ($this->files as $file) {
            $item = array_merge($item, $file->getItems());
        }
        return $item;
    }
}
