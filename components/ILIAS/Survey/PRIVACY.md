# Survey Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Survey component employs the following services, please consult the respective privacy.mds
    - The **Metadata** service contains two branches: LOM and custom metdata. The LOM offers storing person dates like author. Custom metadata do contain user-created metadata sets which may contain personal data, which must be individually checked in the global administration.)
    - The **Object** service stores the account which created the
      object as it's owner and creation and update timestamps for the
      object.
    - [AccessControl](../../Services/AccessControl/PRIVACY.md)
    - [Info Screen Service](../../Services/InfoScreen/PRIVACY.md)
    - The **Conditions** service controls preconditions for repository objects. The survey implements a "Finished" condition.

## Configuration

**Global**

- **Access Codes** Presentation: If activated, access codes in anonymous surveys (setting "Without Names" in survey results privacy settings), which have with access code activated setting "Authentication by Access Code"), will present the access codes in the results. If deactivated the term "Anonymous" will be displayed.

**Survey**



## Data being stored


## Data being presented


## Data being deleted


## Data being exported

