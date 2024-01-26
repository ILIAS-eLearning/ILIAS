<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Preloader;

/**
 * Trait SecureString
 *
 * @internal
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait SecureString
{
    
    protected function secure(string $string) : string
    {
        return htmlspecialchars(strip_tags($string), ENT_QUOTES, 'UTF-8', false);
    }
}
