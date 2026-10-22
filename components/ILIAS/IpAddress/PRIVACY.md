# IpAddress Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).**

### General Information

IP address definitions themselves provide an interface to store IP address ranges, along with a title and description.
These IP address definitions then provide additional functionality to check whether an arbitrary IP address is within
the IP address range(s) defined within an IP address definition.

An IP address range herein consists of either one or two IP addresses:
- If a singular IP address is stored, it will be implicitly used for equality checks (i.e. if an arbitrary IP address equates to the stored IP address).
- If two IP addresses are stored, they will be implicitly used for range checks (i.e. if an arbitrary IP address is within the range outlined by the two stored addresses).

This may then be used to extend access control mechanisms of other components (e.g. Test & Assessment).

### Integrated Services

- The IpAddress component employs the following services,  please consult the respective privacy.mds
    - [AccessControl](../AccessControl/PRIVACY.md)
    - [Object](../ILIASObject/PRIVACY.md)

### Configuration

- **Global**
    - Enable/Disable IpAddress Permissions for object types (Administration > IP Address Definitions > Settings).

## Data being stored

For an IP address definition, the following information is being stored:
- Title
- Description
- Online status

IP address ranges are stored in a separate database table and store the following information:
- `range_id`: Unique identifier of this IP address range
- `definition_id`: Reference ID of the IP address definition using this IP address range.
- `ip_range_from`: IP address, either used as an individual IP address or as a minimum IP address of a range.
- `ip_range_to`: Optional, IP address, used as the maximum IP address of a range.

## Data being presented

Users with the according permissions may see the following:
    - Title
    - Description
    - Online status
    - IP address ranges
for all IP address definitions, within the "IP Address Definitions"
administration page.

When this component is used inside of other components, users may see
    - Title
for all IP address definitions set to "online", within the dependant
component.

## Data being deleted

- When deleting an IP address definition, all associated IP address ranges are deleted.

## Data being exported

- XML exports of IP address definitions contain the following information, see above for more information:
    - Title
    - Description
    - IP address ranges
