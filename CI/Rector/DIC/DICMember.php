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
