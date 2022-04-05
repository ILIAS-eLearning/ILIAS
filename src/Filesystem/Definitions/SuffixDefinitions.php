<?php declare(strict_types=1);

namespace ILIAS\Filesystem\Definitions;

final class SuffixDefinitions
{
    const SEC = ".sec";
    protected array $white_list = [];
    protected array $black_list = [];
    
    /**
     * @param array $white_list
     * @param array $black_list
     */
    public function __construct(array $white_list, array $black_list)
    {
        $this->white_list[] = '';
        $this->white_list = array_unique($white_list);
        $this->black_list = $black_list;
    }
    
    public function getWhiteList() : array
    {
        return $this->white_list;
    }
    
    public function getBlackList() : array
    {
        return $this->black_list;
    }
    
    /**
     * @deprecated Use ILIAS ResourceStorage to store files, there is no need to check valid filenames
     */
    public function getValidFileName(string $filename) : string
    {
        if ($this->hasValidFileName($filename)) {
            return $filename;
        }
        $pi = pathinfo($filename);
        // if extension is not in white list, remove all "." and add ".sec" extension
        $basename = str_replace(".", "", $pi["basename"]);
        if (trim($basename) == "") {
            throw new \RuntimeException("Invalid upload filename.");
        }
        $basename .= self::SEC;
        if ($pi["dirname"] != "" && ($pi["dirname"] != "." || substr($filename, 0, 2) == "./")) {
            $filename = $pi["dirname"] . "/" . $basename;
        } else {
            $filename = $basename;
        }
        return $filename;
    }
    
    /**
     * @deprecated Use ILIAS ResourceStorage to store files, there is no need to check valid filenames
     */
    public function hasValidFileName(string $filename) : bool
    {
        $pi = pathinfo($filename);
        
        return in_array(strtolower($pi["extension"]), $this->white_list)
            && !in_array(strtolower($pi["extension"]), $this->black_list);
    }
}
