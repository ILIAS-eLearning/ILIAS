# LTI Provider Privacy

> Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via the [ILIAS issue tracker](https://mantis.ilias.de) or submit a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

### General information
This component allows to provide learning content to external Platforms / Consumers. ILIAS acts as Provider (LTI 1.1) respectively as Platform (LTI 1.3). Users can switch between the LMS, for example, seamlessly without re-authentication. Additionally, learning progress achieved in the Tool can be transferred back to the Platform /Consumer. For details see PRIVACY.md in Component LTI Consumer.
For improved privacy and enhanced data security, it is highly recommended to use pseudonymisation in the settings of the Platform / Consumer.

### Integrated Components
- LearningProgress
- User

### Data being stored by ILIAS as Tool / Provider 
The LTI Tool / Provider also stores a user identification to match the ILIAS user. The personal data being stored depends on the settings made in the Platform / Consumer. From this ILIAS installation, it cannot be controled how the personal data is dealt with. 
If the "Global Role assigned to LTI Users" is set to "LTI User", external users get can only access the specified resource in ILIAS. 

### Data being presented by ILIAS as the Tool / Provider
For Learning Progress data, please consult the link above.
The same applies to User data in Member Service and the like.  

### Data being deleted
ILIAS as Tool / Provider 
For options for deleting personal data, please consult the link above for USER. 

### Data being exported
No personal data can be exported. 
