# Module cmix readme
This document describes basic concepts related to the specifications of the Experience API (xAPI) (see links below) and cmi5 (see links below).
## Basic technical principles of xAPI
xAPI essentially works with a simple but powerful data model based on “statements”. A statement consists of:
* Actor: a person or group that does something
* Verb: what the actor does
* Object: what the actor works on or interacts with

These statements are encoded in JSON format and can contain additional contextual information that helps to further describe the learning experience. This model allows for the documentation of an almost infinite number of learning actions and scenarios, from simply completing a learning module to interacting with a virtual object.

xAPI plays an important role in current and future learning applications:
* Tracking/monitoring/evaluating learning activities
* Visualizing learning analytics data in dashboards
* Developing innovative e-learning methods with AI-supported adaptivity
* Exchanging learning information between different platforms and tools
* And much more

The possibilities of xAPI go far beyond what older standards such as SCORM offer.

## cmi5
cmi5 was developed as a usage profile of xAPI to bridge the gap between the existing SCORM and xAPI standards. cmi5 leverages the flexibility of xAPI for tracking and adds a set of rules that defines the structuring and reliable exchange of digital learning content in a manner comparable to SCORM. In this way, cmi5 combines the strengths of both systems and offers a more comprehensive and future-proof solution for educational technology.

## Special features of ILIAS

ILIAS supports the xAPI 1.03 and cmi5 specifications, provided that single assignable units are used as usual. The special features of ILIAS lie in the fact that special mechanisms for data protection and data reduction are implemented. Provided that no unusual data transfers are contained in the integrated content, the integrated xAPI/cmi5 content does not send its data directly to a Learning Record Store (LRS), but via ILIAS to the LRS. On the way via ILIAS, data can be dropped (data reduction) and relevant data for ILIAS learning progress can be accessed.

It is recommended to use the pseudonymization options provided by ILIAS. This ensures that no user-identifying information is transferred to embedded content, which is often located on external systems. For more information, please refer to the PRIVACY.md document.

Although not specified in the specifications, ILIAS is able to delete data in a Record Learning Store at specified times. This requires suitable APIs in the Learning Record Store. The Learning Record Store “Learning Locker” has proven itself here. Other LRSs, such as “Yet Analytics” and “Trax,” can also be used. Suitable deletion procedures must then be implemented at the level of these LRSs.

The pseudonymized data can be displayed in ILIAS and assigned to ILIAS users. Currently, these queries are still specific to Learning Locker. The display of the statements is particularly helpful for analysis tasks and support requests.

## Helpful Links

### xAPI
* https://github.com/adlnet/xAPI-Spec
* https://adlnet.gov/projects/xapi/
* https://adlnet.gov/projects/xapi-technical-specifications/
* https://xapi.com/ (Rustici)

### cmi5
* https://aicc.github.io/CMI-5_Spec_Current/
* https://aicc.github.io/CMI-5_Spec_Current/flows/cmi5-overview.html
* https://www.adlnet.gov/projects/cmi5-specification/
* https://adlnet.gov/assets/uploads/cmi5%20Best%20Practices%20Guide%20-%20From%20Conception%20to%20Conformance.pdf
* https://www.adlnet.gov/assets/uploads/Bridging%20the%20SCORM%20and%20xAPI%20Gap%20-%20the%20Role%20of%20cmi5.pdf
