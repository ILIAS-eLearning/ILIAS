# The Development Process

ILIAS software is developed in a community-based open source process and published under the [General Public Licence version 3](https://www.gnu.org/licenses/gpl-3.0.html) (see also [licence](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/LICENSE) information in source code). This page describes how you can get involved in the software development of ILIAS and what you have to take into consideration. The ILIAS feature development process consists of these major steps:

## 1. Having an idea
Anyone who envisages an improvement or extension of ILIAS should share ideas with the user community. An optional first step is to discuss your idea in the ILIAS forums. A mandatory step to bring anything into the ILIAS development is to add your idea to the Feature Wiki and to put it onto our Jour Fixe agenda.
Notice: You do not need to have any funding suggesting a new feature. But the chance that your suggestion becomes part of ILIAS is much higher when you - or someone else - has funding to pay the developers.

## 2. Community Discussion
In the Feature Wiki all community members are invited to share their ideas and comments. Often the responsible maintainer (see list of maintainers) of the feature is also adding comments or ideas to a feature request. This step of the development process should create a common understanding of the feature and generalise its concept to make the feature usable for as much users as possible.

## 3. Jour Fixe Decision
The decision whether a new feature should be part of a future ILIAS release or not is made in the bi-weekly Jour Fixe. In this meeting the ILIAS Product Manager, members of the Technical Board, developers of ILIAS components and interested community members meet to discuss the current ILIAS development. The final decision is taken by Product Manager and responsible maintainer. Decision and reasons are documented in the agenda of the meeting and published in the Feature Wiki, see list of Jour Fixes.

Notice: Everytime you want the Jour Fixe to get involved in the discussion or to make a decision, put the topic on the Jour Fixe agenda. Sometimes the core team asks for additional concepts papers, use cases or mock-ups, especially when it comes to more complex features. All this is documented in the Feature Wiki.

## 4. Funding
Every suggested feature needs funding to be implemented. If funding is not settled yet, the community is asked to look for supporters of new features. Sometimes multiple institutions join to fund a new development. For selected features the ILIAS society is setting up crowdfunding activities, see here.

## 5. Implementation
Usually, new features extend existing components of ILIAS. In this case, there are two ways to implement the new feature depending on which model is applied to the related component:

1. In case the component is developed under the coordinator model, every developer can implement the feature and make a pull request (PR) against the trunk of ILIAS. One of the responsible coordinators will revise the PR, maybe ask for changes and finally approve the code changes. The PR then can be merged to trunk.
2. In case the component is developed under classic maintainership, the first maintainer usually takes care of the implementation and commits the changed code to trunk.

Which component is currently developed under which model is documented on the page Component Maintainers and Testers. The same page is also available in GitHub.
Everybody who wants to provide pull requests for extending existing components or even add new components to ILIAS has to follow our rules for getting involved as developer. Every developer has to respect the guidelines described within this document.
Developers need to create their own feature branches for development and approval by customer before merging code to the trunk following the procedure above. All new features must be integrated until Coding Completed (see the timeline of each ILIAS version in the Feature Wiki).

## 6. Testing and Bug Reporting
New source code has always to be tested locally by the programmer on his or her installation before committing it to the GitHub repository of ILIAS. Every new feature is also tested by the customer that has ordered this implementation. If the customer has accepted the feature it is committed to the trunk of ILIAS.
Test cases to test this feature have to be available with the implementation of the feature (with the first beta release at the latest). They can be written by the customer or the service provider. During the beta phase of the upcoming version new and existing features are tested by voluntary community testers, see list of component testers. Testing is done on a dedicated test installation and supported by Testrail test case management system.
All bugs discovered in maintained ILIAS versions have to be reported to our Mantis bug tracker where they are assigned to the responsible maintainer and treated according to the defined bugfixing process, see below.

![Schematic of the bug fixing process](https://files.ilias.de/images/bug_fixing_process.png)
*Bug fixing process as defined and accepted by General Meeting of Members in Bern 2015*

## 7. Documentation
The user documentation of ILIAS is currently based on the instructions coming from the ILIAS online help and will be extended step-by-step in the next time. The former user documentation (Docu World) from ILIAS service provider Qualitus is no longer maintained. The online help for ILIAS is coordinated by the Head of Help and written and updated by a group of community members (currently only available in German).

## 8. Release and Maintenance
In the last step a new release is packed and published. Major ILIAS releases are published once a year. Stable versions are to be used for productive systems. For each major release a number of maintenance releases will be published for up to two years. Information about every published release is added to the Roadmap and Releases document.
Major releases are identified by the first number of the release, e.g. 6.0, 7.0, 8.0. Major releases include new features and are published once a year.

For maintenance releases (aka bug fix releases) the last number is incremented, e.g. 7.9, 7.10, 7.11. Bug fix releases do not include new features. Upgrading should be usually painless and customized templates or style sheets should not be affected.

### Typical Major Release Timeline

- New features can be suggested for an upcoming release until feature freeze, usually end of April. Already before and also after feature freeze, the core team decides which features will go into the new release and which not. The decisions are documented in the feature wiki.
- Coding of the new feature can already begin as soon as the ILIAS trunk is opened for the new version. This happens straight after Coding Completed of the former ILIAS version and the creation of a release branch for this version.
- All features for an upcoming ILIAS version have to be fully developed until Coding Completed. After Coding Completed only bug fixes and usabilty improvements are allowed to commit.
- Community testing starts as soon as the first beta release of an upcoming ILIAS version has been published. The ILIAS society provides general testing installations for all for each major release, e.g. test7.ilias.de, test8.ilias.de.
- Our target for a stable major release is usually March.