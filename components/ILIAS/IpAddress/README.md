# IpAddress Component

This directory contains the IpAddress component, which is responsible for managing named IP address
ranges (so-called IP address definitions) for later use in access control by other components.

Currently, this component is integrated into the Test component, with plans to integrate into the User
component (by replacing `ClientIP` (`components/ILIAS/User/src/Profile/Fields/Standard/ClientIP.php`)
once `ilFormPropertyGUI` has been removed.

## Conceptual summary

This component stores named IP address ranges (so-called IP address definitions) as ILIAS objects.
These objects have the following properties:
- Title
- Description
- Online status
- Array of IP address ranges

An IP address range can consist of one ("check if the user's IP address is N")
or two ("check if the user's IP address is between N and M") IP addresses.

Only IP address definitions that are set to "online" can be used in other components.

## Derived Tasks

If your component wants to access IP address definitions, the following APIs might
be of use to you:

1.  Using the `search` method within `ilObjIpAddressDefinition`, a list of "online" IP
    address definitions whose title matches the search string is returned.
2.  An `IpAddressRangeRepository` can be instantiated using the `ref_id` of an IP address
    definition. Using this object, one can check whether a given IP address is within
    the range(s) outlined within an IP address definition.
3.  This component provides `ilObjIpAddressDefinitionInputFieldGUI`, which is a pre-built
    UI component which can be used to query the user for IP addresses, IP subnets and IP
    address definitions. We recommend that you use this component in yours also, as this
    provides a unified interface to interact with IP addresses. Note that this component
    itself simply returns whether a certain IP address is within the range(s) outlined
    within an IP address definition, but **does not provide any functionality for access
    control**. You will need to implement this within your component.

# JF Decisions

# Metrics

