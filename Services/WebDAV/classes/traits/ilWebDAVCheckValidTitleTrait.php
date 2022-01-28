<?php declare(strict_types = 1);

trait ilWebDAVCheckValidTitleTrait
{
    protected function isDAVableObjTitle(string $title) : bool
    {
        if ($this->hasTitleForbiddenChars($title) || $this->isHiddenFile($title)) {
            return false;
        }
        
        return true;
    }
    
    protected function hasTitleForbiddenChars(string $title) : bool
    {
        foreach (str_split('\\<>/:*?"|#') as $forbidden_character) {
            if (strpos($title, $forbidden_character) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function isHiddenFile(string $title) : bool
    {
        $prefix = substr($title, 0, 1);
        return $prefix === '.';
    }
    
    protected function hasValidFileExtension(string $title) : bool
    {
        return $title === ilFileUtils::getValidFilename($title);
    }
}
