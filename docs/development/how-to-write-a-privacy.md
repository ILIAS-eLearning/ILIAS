# How to write a PRIVACY.md

Version 2.3 [updated 2025-02-26]

## 1 Purpose and target group

This guide provides recommendations for structuring and composing PRIVACY.md files for ILIAS. It is designed to help authorities create PRIVACY.md files for their components. The target group of the PRIVACY.md files is the e-learning team of an educational provider, not other authorities. These teams need to provide relevant information to the officer responsible for ensuring compliance with the EU General Data Protection Regulation (GDPR). Please use clear and accessible language to make the information easy to understand.
Your feedback and change requests are welcome. Please send them to toedt@leifos.com and elyesa.seidel@hhu.de.

## 2 Where to write » Use Git

- Create **one** file named “PRIVACY.md” **per component**.
- Submit a **Pull Request** for each PRIVACY.md file at the top level of the respective component directory in Git:
  `ILIAS/blob/trunk/components/ILIAS/NameOfComponent/PRIVACY.md`
- In the future, a tool will aggregate all PRIVACY.md files into a comprehensive privacy document tailored to specific installations.

## 3 What to include » Use the template

To extend or add the privacy documentation, base your Pull Request on the following template:

### [Name of the component] Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via the [ILIAS issue tracker](https://mantis.ilias.de) or submit a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).**

### General information

- Indicate any special conditions, such as:
  - If the pool appears like a separate component but is deeply entangled with another component (e.g., a test).
  - If the learning progress continues tracking data even when disabled.
- If the component is straightforward, this section may be omitted.

### Integrated components

- List all components this component employs.
    - Provide a brief description of their functionalities.
    - Link to their respective PRIVACY.md files (if available).
- To organize these references:
  - Create a configuration file for setup by copying the **minimal-config.json** file located in the setup directory.
  - Ensure that references are listed in the same order as the tabs.
-	The PRIVACY.md files do **not** cover issues arising from **user-generated content**.
-	If your component makes use of another component that does not yet have a PRIVACY.md file,  list it anyway to help identify gaps.
- **Example**: The HTML Learning Module employs the following components (please refer to their respective PRIVACY.md files):
  - Learning Progress
  - Metadata
  - Object
  - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md)
  - [InfoScreen](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/InfoScreen/PRIVACY.md)

### Data being stored

- List **all personal data** that this component stores (`INSERT`and `UPDATE`):
  - If the component only reads or references personal data from another component, describe it in that other component’s PRIVACY.md file instead.
  - Be **specific** — only personal data should be listed, not generic or non-identifiable data.
-	Clearly indicate the **purpose** of each type of personal data stored.
-	Exclude any personal data that might reside in **user-generated content**.

### Data being presented

-	Specify **what** is presented, **where** it is presented, and **who** can see it based on permissions.
-	Use **quotation marks** when referring to permissions, e.g., “Edit Settings” permission.
-	Avoid role-based terminology, as roles vary between installations. Instead, use the neutral term **"person"** and specify the required permission.
-	If data visibility is controlled by a setting rather than a permission, indicate this.
- **Example**:
  -	Tags are presented to each person who has created them.
  -	On **Info-tabs**, all tags from all users are visible if the **respective setting is enabled**.
  -	An overview of users assigned to competence profiles is visible to persons with **Administration access**.

### Data being deleted

-	Explain **who** can delete personal data and **where** they can do it.
-	Use the term “delete from trash” when referring to **permanent deletion** from ILIAS.
-	Mention any **dependencies** affecting deletion (e.g., related data that must be removed first).

### Data being exported

-	Describe **which** personal data can be exported and **where/how** users can export it.

## 4 Next steps » Submitting PRIVACY.md for review

Once you have outlined the necessary details using this template, you may choose to submit your PRIVACY.md for review by technical editors or the SIG Law & E-Learning. They can assist with:
  -	Polishing the English for clarity and consistency.
  -	Ensuring alignment with other PRIVACY.md files.
  -	Improving overall intelligibility.
 
**We appreciate your contribution to maintaining transparent and clear privacy documentation for ILIAS!**







