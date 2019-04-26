# Rule Enginge for ILIAS
This namespace provides a small framework for creating ILIAS specific rules that can be checked synchronized and unsynchronized.

Overview and Glossary for this namespace:


| Name | Description | Example |
|------|-------------|---------|
| Rule | A Rule has Events and that will be triggered | ...
| Specification | ... | 
| Action |... | 
| Context | ... | 


Usage
-----
$rule_factory = $DIC->RuleEnginge()->RuleFactory();

// 1. Write a specification
$specification_factory = SpecificationFactory::andX(
						SpecificationFactory::is('institution', 'ACME'),
						SpecificationFactory::containes('email', '@acme.com')
                       );
                       
                       
//2. Create the rule
$rule_factory->create(new UserAction,$specification_factory);

//4. Check the rule
$rule_factory->isSatisfiedBy(new UserContext());
 
