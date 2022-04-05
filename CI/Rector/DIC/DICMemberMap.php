<?php declare (strict_types=1);

namespace ILIAS\CI\Rector\DIC;

class DICMemberMap
{
    const TPL = 'tpl';
    protected array $map = [];
    
    public function __construct()
    {
        $tpl = new DICMember(
            self::TPL,
            \ilGlobalTemplateInterface::class,
            ['ui', 'mainTemplate'],
            'main_tpl'
        );
        $tpl->setAlternativeClasses([\ilTemplate::class, \ilGlobalTemplate::class, \ilGlobalPageTemplate::class]);
        $this->map[self::TPL] = $tpl;
    }
    
    public function getByName(string $name) : DICMember
    {
        if (!isset($this->map[$name])) {
            throw new \InvalidArgumentException("The dependency '$name' is currently not configured");
        }
        return $this->map[$name];
    }
}
