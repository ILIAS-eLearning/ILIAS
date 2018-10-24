# Want to Contribute? Great!

## Table of Contents

<!-- MarkdownTOC depth=0 autolink="true" bracket="round" autoanchor="true" style="ordered" indent="   " -->

1. [Who is a contributor?](#who-is-a-contributor)
1. [How to contribute?](#how-to-contribute)
   1. [Pull Request to the Repositories](#pull-request-to-the-repositories)
      1. [Rules for Contributors](#rules-for-contributors)
      1. [Rules for Maintainers assigned to PRs](#rules-for-maintainers-assigned-to-prs)
   1. [Want to Contribute Something else than Commits?](#want-to-contribute-something-else-than-commits)
1. [List of Labels?](#list-of-labels)

<!-- /MarkdownTOC -->

<a name="who-is-a-contributor"></a>
## Who is a contributor?

In general we consider everyone who takes part in the development of ILIAS a
contributor, where the contribution could take various forms, e.g. testing,
creating feature request, writing documentation, reporting security issues. We
aim to include everyone performing these or similar activities in our processes.

For practical reasons we need to define a contributor to be everyone who wants
to contribute commits to our repository for now. We trying to figure out ways to
also include Testers, Translators, Authors and other people into the processes
described here. If you want to contribute to said activities please have a look
[here](contributing.md).

As a contributor you will be named in the release notes of our major relases
with your name and your organisation as we find them in our commit history and
in your profile on GitHub. If your don't want to be listed, please write a short
mail to the [Technical Board of the ILIAS society](mailto:tb@lists.ilias.de).

<a name="how-to-contribute"></a>
## How to contribute?

<a name="pull-request-to-the-repositories"></a>
### Pull Request to the Repositories

Pull requests (PRs) will be assigned by the [Technical Board
(TB)](http://www.ilias.de/docu/goto.php?target=grp_5089&client_id=docu) to the
responsible maintainer. The TB will also help to resolve problems with PRs and
associated processes, if you require mediation please leave a mention via
`@ILIAS-eLearning/technical-board` in the discussion of the PR.

Please make sure to understand that the ILIAS society has a [process for
functional feature request](https://www.ilias.de/docu/goto_docu_wiki_wpage_788_1357.html) 
before starting to create your PR. Your PR should thus only contain bug fixes or 
non-functional changes to our code base.

<a name="rules-for-contributors"></a>
#### Rules for Contributors

We are happy that you want to contribute. To enable us to merge your PRs in our
code please make sure:

* that your PR has a description that tells what is changed and why - with a
  size relative to the changes
* that your PRs is minimal - prefer to make two small PRs instead of one big PR
* that you discuss huge PRs with the responsible maintainers in advance - this
  will save your time if the maintainers do not agree with your proposed change
* that you create commits of self-contained logical units with concise commit
  messages and no unnecessary whitespace - this will help reviewers to
  understand what you did
* that your code could be understood and is documented - this will help
  reviewers as well
* that your commit follows the [ILIAS coding
  guidelines](http://www.ilias.de/docu/goto_docu_pg_202_42.html) - this is a
  bare minimun of style we want to maintain for new code
* that your are approachable for questions of reviewers

If your PR contains a bugfix please reference the number of the mantis ticket
in the title `12345 - To many spaces`, link the ticket in the description and
label the ticket with `bugfix`. You may make one PR per affected branch.

Please label non-bugfix PRs as `improvement`.

<a name="list-of-labels"></a>
## List of Labels

There are currently the following labels used for Pull-Requests. These labels will 
be assigned by the Technical Board or Maintainers:

| Label           | Description                                                                                                                                                               |
|-----------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| bugfix          | PRs with the label `bugfix` propose a solution for a reported bug in the official Bugtracker https://mantis.ilias.de                                                      |
| improvement     | The label `improvement` is used for PRs which propose a general improvement of code or documentation which is not related to a bug.                                       |
| roadmap         | The label `roadmap` is assigned to PRs that contain strategical or tactical discussions of technical topics regarding the future of a component.                          |
| jour fixe       | PRs which should be discussed during the next Jour Fixe are labeled with this `jour fixe`. Please set this label at least 2 days before the envisaged date of Jour Fixe.  |
| kitchen sink    | All contributions to the Kitchen Sink Project are labeled accordingly.                                                                                                    |
| technical board | This label is given for PRs which will be discussed in a meeting of the Technical Board. The label will be removed after the discussion.                                  |

<a name="rules-for-maintainers-assigned-to-prs"></a>
#### Rules for Maintainers and Coordinators assigned to PRs

As an FOSS community we should be glad that people want to contribute code to
our project as this reflects usage of our project. To show this when handling
PRs, please make sure

* that you react to every PR assigned to you by the TB within 21 days - at least
  with a thank you and a target date if your schedule is tight
* that you give at least a brief statement why you close a PR if you reject one
* that you merge the changes in the PR in other branches if required

<a name="want-to-contribute-something-else-than-commits"></a>
### Want to Contribute Something else than Commits?

We are happy to get contributions that are no commits as well. There are many
other things you could contribute to ILIAS:

* **Ideas for new Features**: The development of ILIAS is driven by requirements
  from the community. Contribute your ideas via [feature requests]
  (http://www.ilias.de/docu/goto.php?target=wiki_5307&client_id=docu#ilPageTocA119).
* **Bug Reports**: We do our best, but ILIAS might contain bugs we do not know
  yet. Check out how the ILIAS Community handles [bug reports]
  (http://www.ilias.de/docu/goto.php?target=wiki_5307&client_id=docu#ilPageTocA115).
* **Information about Security Issues**: Check out how the ILIAS community
  handles [security issues]
  (http://www.ilias.de/docu/goto.php?target=wiki_5307&client_id=docu#ilPageTocA112). 
  Reporter of security issues will also be named in the release notes.
* **Time for Testing or Testcases**: We always need people that contribute
  testcases and perform them before new releases. Please have a look
  [here](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html) (German only).
  An English translation of the Tester Guide will be provided in summer 2017. If
  you have questions before that, do not hesitate to contact our test case
  manager Fabian Kruse (fabian@ilias.de).
