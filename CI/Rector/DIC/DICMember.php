<?php declare (strict_types=1);

namespace ILIAS\CI\Rector\DIC;

class DICMember
{
    protected string $name;
    protected string $property_name;
    protected string $main_class;
    protected array $alternative_classes = [];
    protected array $dic_service_method = [];
    
    public function __construct(string $name, string $main_class, array $dic_service_method, string $propery_name)
    {
        $this->name = $name;
        $this->main_class = $main_class;
        $this->dic_service_method = $dic_service_method;
        $this->property_name = $propery_name;
    }
    
    public function setAlternativeClasses(array $alternative_classes) : void
    {
        $this->alternative_classes = $alternative_classes;
    }
    
    public function getName() : string
    {
        return $this->name;
    }
    
    public function getMainClass() : string
    {
        return $this->main_class;
    }
    
    public function getAlternativeClasses() : array
    {
        return $this->alternative_classes;
    }
    
    public function getDicServiceMethod() : array
    {
        return $this->dic_service_method;
    }
    
    public function getPropertyName() : string
    {
        return $this->property_name;
    }
    
}
