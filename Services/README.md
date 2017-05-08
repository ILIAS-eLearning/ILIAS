# Services

Services provide general functionalities used in the modules or in other services,
e.g. the role based access system or the news system.

If you want to provide a new service and parts can be almost completely abstracted
from other ILIAS dependency, they SHOULD be placed in the src directory.

## Guidelines for the ./Services internal interface documentation

1. Each subdirectory under ./Services SHOULD contain a README.md file
2. All internal interfaces provided by the service SHOULD be documented in the README.md file.
3. Breaking changes MUST NOT be introduced in maintenance branches.
3. Changes MUST be handed in via a pull request with a certain naming scheme (TBD).
	1. PR must be announced and decided on in a Jour Fixe meeting.
	2. All developers have 8 days for sending a veto to tb@lists.ilias.de.
	3. In case of a veto the technical board will decide on further steps.
	4. If no veto has been handed in, the PR can be merged to the trunk.
