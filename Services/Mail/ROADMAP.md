# Roadmap

## Mid Term

### Refactor Registration Process of Mail Template Context 

Currently, mail template contexts can be defined in the component XML file.
These files are processed by \ilMailTemplateContextDefinitionProcessor::beginTag
and \ilMailTemplateContextDefinitionProcessor::endTag, which are called by the
\ilComponentDefinitionReader.

Current Problems:
* \ilMailTemplateContextService::getContextInstance is used in multiple contexts.
* Certain dependencies (retrieved from `$DIC`) do not exist or are replaced with fake objects during setup,
  but the actual services are required in the constructors of the concrete implementations.
* There should be an interface segregation for the registration of mail template contexts and the actual provided
  context which is responsible to provide/replace placeholders.
* To solve the problem, the mail context implementations are currently created with the `Reflection API` in the setup context.