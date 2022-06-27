# Review

A review is given by a reviewer on code contributions (see: [contributing.md](contributing.md)) of
a reviewee. There are no guidelines on how review has to be performed in ILIAS, however here we propose a pattern
inspired by an article of Dan Munckton in [How to be a kinder more effective code reviewer](https://cultivatehq.com/posts/how-to-be-a-kinder-more-effective-code-reviewer/).

ILIAS knows two different types of reviews:
- Reviews that are given by developers to each other as part of the process to introduce improvements through Pull Requests
- Reviews that are conducted regularly as part of the Quality Assurance Process as a way to assess Code Maturity.

## General Pattern
In our experience many conflicts arise due to feedback written in a fashion which makes it hard to understand,
what exactly is asked of a reviewee. The pattern adapted from suggestions by Munckton is aimed
to make reviews easier to read and to leave less room for misunderstandings and communicational deadlocks.

We try to lay a strong focus on making feedback **actionable**, meaning the reviewee should have
a clear picture on how to react. Inspired by Munckton (see below for more details) we propose
to use the [template provided below](#template-for-reviewing-prs) to reply to PRs.

## Reasoning
Munckton introduces the term **actionable** for feedbacks. An **actionable** feedback is one that
can directly be reacted upon. He claims Questions, Suggestions and Change Requests
to be the most relevant types of such statements. He gives the following examples of those three statements:

- Question: could you explain why this ended up having to be here?
- Suggestion: you may have come across this already, but we might be able to use XYZ for this.
- Change request: sorry, I appreciate the effort it took to get it this far. But the team
already agreed to solve this using XYZ. So I'm going to have to ask you to rework this. Let me know if I can help.


A fourth type he sometimes uses is the reaction, which is not actionable, but might help to express emotions such as thanks.

He further suggests to always keep things as clear as possible by using the following pattern:

>[type]: [actionable request]

>[rationale or discussion]

Whereas:
- type: one of Question, Suggestion or Changes Request.
- actionable request: is a short clear question or statement, which makes clear how the reviewee
should respond.
- rational or discussion: Possible further explanations of the reasoning behind the request.

An example could be:
>Suggestion: use XYZ.

>You may have come across this already, but we might be able to use XYZ for this because blah blah blah etc.

We adapted this slightly by bundling questions, suggestions and change requests in grouped listings and placing
the reactions up front.

## Inline Comments
Github makes it very easy to leave inline comments. However, in the past, we observed, that large
numbers of inline comments make a review hard to read and understand. We therefore propose
only the leave inline comments for trivial directly actionable suggestions and change request, that
do not need an rational (E.g. *Change Request: typo in word actionalbe*).

Do not leave any reactions in inline comments. They tend to crowd the discussion without giving
the possibility of being resolved (seens not actionable). Move them to the summary.

## Quality Assurance Reviews
With the development of ILIAS 8 and the Update of ILIAS to run with PHP 8 the Technical Board and the
Product Manager introduced mandatory reviews for all work packages based on pre-agreed criteria. It was
decided to continue this pattern and thus starting with the development of ILIAS 9 regular reviews on
parts of the code are conducted during the beta-testing. The ILIAS Open Source e-Learning e.V. will take
care of coordinating funding for these reviews.
Reviews will be conducted on:
- All components that have received funding for refactoring. In this case the review is part of the work package.
- As many randomly selected components as funding allows.

The criteria for these reviews are defined by the Technical Board and the Product Manager and implement
a progressive path to a continously improving code base. The Criteria are published as part of [the Template below](#template-for-quality-assurance-reviews).

## Templates
### Template for Reviewing PRs
```
Hi @[name_of_reviewee]

Thank you a lot for contributing to ILIAS.

[reaction_not_actionable]

Please answer the following questions:
- [ ] [question1]? [optional: reasoning behind the question]
- [ ] [question2]? [optional: reasoning behind the question]
- ...

Please consider the following suggestions. You do not need to follow those, but please indicate shortly why you prefer to do otherwise:
- [ ] [suggestion1] [optional: reasoning behind the suggestion]
- [ ] [suggestion2] [optional: reasoning behind the suggestion]
- ...

Please implement the following changes:
- [ ] [change_request_1] [optional: reasoning behind the suggestion]
- [ ] [change_request_2] [optional: reasoning behind the suggestion]
- ...

[optional_comments_on_how_to_proceed]

kindly,
@[your_username]

```
This might look as follows:

```
Hi @klees

Thank you a lot for contributing to ILIAS.

We highly apreciate the effort you took to improve the JS in the KS component. I especially
like the new proposed pattern to tackle the scoping issue.

Please answer the following questions:
- [ ] Why did you not use ECMAScript 6? Note that Bootstrap 4 makes use of it.

Please consider the following suggestions. You do not need to follow those, but but please indicate shortly
why you prefer to do otherwise:
- [ ] Change XY to pattern XZ. We believe that pattern XZ might be superior. However, I am not completely sure.

Please implement the following changes:
- [ ] Reword the function addProperty to withProperty. withProperty is in line with our naming scheme used
  in othe methods of the framework.


Please give a feedback until the end of the week indicating how long it will take to answer
the questions given, react to the suggestions and implement our change requests.


kindly,
@amstutz

```

### Template for Quality Assurance Reviews

Please commit the following file as part of the review to the root of the component.
To measure the statistics you can use:
- Logical Lines of Code: [phploc](https://github.com/sebastianbergmann/phploc): `php phploc.phar <Path to Component>`
- Nr of Tests: phpunit: `./libs/composer/vendor/phpunit/phpunit/phpunit --list-tests <Path to Component Test Suite File>`
- Nr of LoCs changed in last 365 days: `git diff --shortstat HEAD $(git log --since=365.days --oneline Services/WebDAV/ | tail -1 | cut -f 1 -d " ")^ <Path to Component>`
- Nr of Commits in last 365 days: `git log --since=365.days --oneline <Path to Component> | wc -l`
- To check the general code quality: `./CI/PHPStan/run_checks.sh <Path to Component`.

#### If the file already exists:
- Please update the sections 'Maintenance' and 'Statistics'.
- Please add the sections 'Criteria based Review for [ilias_version]' und 'Qualitative Review for [ilias_version]'
  **right below** the section 'Statistics'

#### Review.md

```
# Review of `[name_of_component]` by [name_of_reviewer] at [date_of_review]

## Maintenance
- Maintainers/Coordinators:
- Committers in last 365 days:

## Statistics
- Logical Lines of Code:
- Nr of Tests:
- LoCs changed in last 365 days:
- Nr of Commits in last 365 days:
- Nr of Open Mantis Issues:
- Mantis Issues resolved in last 365 days:
- Nr of Votes in last 365 days:
- Nr of Votes for currently open issues:

## Criteria based Review for [ilias_version]

- [ ] `[name_of_component]`and its administration can be used in devmode with PHP 8 and 8.1 without producing PHP-specific errors
- [ ] The code style is compliant with the [ILIAS Coding Style](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/coding-style.md)
- Globals:
	- [ ] The code contains no $_GET / $_POST / $_REQUEST / $_SESSION
	- [ ] Access to $_GET-parameters happens through the request-wrapper and only in constructors
	- [ ] Direct access to $_POST parameters through the request-wrapper is only used exceptionally and explained in a
	      comment next to the access.
- [ ] There are at least as many unit tests as in the last ILIAS Version and all tests pass. In cases where a feature is
      completely removed from the ILIAS core or where code is centralized, the number of unit tests may exceptionally decrease.
- [ ] All external libraries are up to date and compatible with PHP 8 and 8.1
- Types:
	- [ ] Explict PHP types are used (type hints, return types, typed properties) unless this is not possible due to
	      missing support for Union-Types in PHP 8.
	- [ ] The type `object` is not used
	- [ ] `Mixed` is only used instead of a generic
	- [ ] No named parameters are used
- [ ] General Code Quality:
    - [ ] There are no accesses to unknown classes
    - [ ] There are no accesses to unknown methods
    - [ ] There are no usages of undeclared class properties
    - [ ] There is no dead/unreachable code
    - [ ] There are no accesses to methods that are potentially missing (e.g. when Union Types or Magic Functions are used)
    - [ ] All parameters and return values are either typed or their expected type is declared in a DocBlock, but not both
    - [ ] No methods are called on nullable objects without checking

## Qualitative Review for [ilias_version]
- Review text
- Exemplary Elements or Patterns in this Component

```
