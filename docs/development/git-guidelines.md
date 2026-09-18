# Git Guidelines

This document defines the Git practices we live by inside the ILIAS community when working on this repository. Its
primary purpose is to ensure transparency, consistency, and clear responsibility when contributing code, reviewing
changes, and integrating them into maintained branches. It also serves as a reference for [authorities who sign off on
code changes](./maintenance.md#authorities).

## 1. Core Principles

All rules and recommendations in this document follow a small set of principles:

- **Transparency**: Every code change ****MUST**** be attributable to a specific cause (issue, feature, or release) and
to the discussion that led to its acceptance.
- **Atomicity**: Each commit **SHOULD** leave the repository in a valid, buildable, and deployable state.
- **Authority**: Changes **SHOULD** be reviewed and explicitly approved by defined authorities in a visible manner.
- **Consistency**: Changes **MUST** be applied to all maintained versions of ILIAS in a controlled and reproducible way.

## 2. Commit Types and Semantics

We distinguish between four kinds of commits. The commit type is mandatory and is a first step to set expectations
about its changes.

- **Bugfix**: Fixes broken behaviour and/or addresses a specific issue reported in the issue tracker.
- **Feature**: Introduces, changes, or removes functionality.
- **Refactoring**: Improves or reorganises the internal structure of code without changing its behaviour. These changes
do not necessarily address a reported issue or implement a feature.
- **Release**: Updates version numbers and dump files.

Every commit **MUST** only be associated to exactly one of these types. Mixing these concerns is not possible.

## 3. Commit Message Conventions

Commit messages **MUST** follow a strict structure to ensure readability and transparency. All authorities who sign off
on code changes **MUST** ensure compliance. Commit messages **MUST** be written in English.

### 3.1 Bugfix

```
[FIX] #<issue-no> <component>: <summary>

<description>
```

* `<issue-no>`: the issue number being addressed, if there is any.
* `<component>`: the ILIAS component who owns the functionality that is affected by the changes. Multiple components
**MUST** be separated by `/` (no spaces). Use [component substitutes](#35-components-and-component-substitutes) if there
is no clear owner of an affected file(s).
* `<summary>`: a concise, factual description of the change. Keep this under 50 characters if possible.
* `<description>` (optional): further details explaining the change.

Resulting in e.g. `[FIX] #12345 UI: open dropdown menus again`

### 3.2 Feature

```
[FEATURE] <component>: <summary>

<description>
```

* `<component>`: the ILIAS component who owns the functionality that is affected by the changes. Multiple components
**MUST** be separated by `/` (no spaces). Use [component substitutes](#35-components-and-component-substitutes) if there
is no clear owner of an affected file(s).
* `<summary>`: a concise description of the functional change. Keep this under 50 characters if possible.
* `<description>`: a detailed explanation of the implementation or behaviour. A commit introducing a feature **SHOULD**
provide a link to the corresponding feature request and/or relevant background information.

Resulting in e.g. `[FEATURE] GlobalScreen: contribute head resources`

### 3.3 Refactoring

```
[REFACTOR] <component>: <summary>
 
<description>
```

* `<component>`: the ILIAS component who owns the functionality that is affected by the changes. Multiple components
**MUST** be separated by `/` (no spaces). Use [component substitutes](#35-components-and-component-substitutes) if there
is no clear owner of an affected file(s).
* `<summary>`: a concise description of the structural change. Keep this under 50 characters if possible.
* `<description>` (optional): further details explaining the motivation or scope of the refactoring.

Resulting in e.g. `[REFACTOR] Setup: improve objective handling`

### 3.4 Release

```
[RELEASE] <version>
```

* `<version>`: the release version, prefixed with lowercase `v`, mirroring the associated Git tag.

Resulting in e.g. `[RELEASE] v11.0` or `[RELEASE] v11.0-beta`

## 3.5 Components and Component Substitutes

Most commits can be attributed to a specific ILIAS component. For other locations inside the repository without a clear
component associated with it, the following substitutes must be used instad of the commits `<component>` placeholder:

| Substitute      | Applies to                                                                        | Examples                                       |
|-----------------|-----------------------------------------------------------------------------------|------------------------------------------------|
| `Documentation` | Changes to `./docs` and other top-level documentation files.                      | `README.md`, `LICENSE`                         |
| `CI`            | Changes to automation, QA tooling, scripts, or related configuration.             | `./scripts`, `./.github`, `./captainhook.json` |
| `Dependency`    | Changes to `composer` or `npm` related files, which **SHOULD** be auto-generated. | ``composer.lock`, `package-lock.json`          |
| `Authority`     | Changes to `./docs/development/maintenance.md` that affect authority changes.     | `./docs/development/maintenance.md`            |

Resulting in e.g. `[FIX] CI: update php-cs-fixer config`

If none of these substitutes match the changes of the commit, the component or substitute **MAY** be omitted for the
time being. This document **COULD** be extended in future to take more cases into account.

## 3.6 Commits Originating from Pull Requests

Commits that originate from pull requests **MUST** preserve a reference to the GitHub conversation. This ensures that
design decisions and review outcomes ARE easily traceable and accessible. GitHub will automatocailly create link
references to pull requests mentioned inside the commit message like below. When later cherry-picked to other branches
of the repository, the pull request timeline will be updated as well.

So when integrating a pull request, the final commit message **MUST** be amended as follows:

```
<commit-message> (#<pr-no>)
```

* `<commit-message>`: a commit message that complies to the conventions above.
* `<pr-no>`: the pull request number as shown on GitHub.

If there is more than one commit message, the PR appendix **SHOULD** either be added to all commits, or the most
relevant commit. This is for the code-authority to decide.

Resulting in e.g. `[FIX] #12345 UI: open dropdown menus again (#54321)`

## 4. Integrating Commits

To preserve a linear history there **MUST NOT** be any merge commits inside the repository. Hence, all integrations
**MUST** be performed using one of the following strategies.

### 4.1 Rebase and Merge

When individual commits are meaningful and worth preserving separately, you **SHOULD** use this strategy.

```bash
git checkout "<source-branch>"
git rebase "<source-branch>" "<target-branch>"
git checkout "<target-branch>"
git merge --ff-only "<source-branch>"
```

Where as the placeholders **MUST** be replaced like:

- `<source-branch>`: the name of your source branch that contains the individual commits.
- `<target-branch>`: the name of your target branch where your commits should be integrated.

### 4.2 Squash and Merge

When individual commits are not meaningful and worth preserving separately, you **SHOULD** use this strategy.

```bash
git checkout "<target-branch>"
git merge --quash "<source-branch>"
```

Where as the placeholders **MUST** be replaced like:

- `<source-branch>`: the name of your source branch that contains the individual commits.
- `<target-branch>`: the name of your target branch where your commits should be integrated.

### 4.3 Cherry-pick

When one or more integrated commits from one branch of the repository need to be transferred to another, you **SHOULD**
use this strategy.

```bash
git checkout "<target-branch>"
git cherry-pick "<commit-id>"
# possibly repeat for other commits
# rebuild assets if need be
```

Where as the placeholders **MUST** be replaced like:

- `<target-branch>`: the name of your target branch where your commits should be integrated.
- `<commit-id>`: the hash of the commit which should be integrated from your source branch.

Where possible, commits that affect compiled assets **SHOULD** be kept separate from the changes that drive them. This
simplifies cherry-picking: the source changes can be cherry-picked normally, and the assets are then rebuilt on each
target branch. Compiled assets are e.g. the `./templates/default/delos.css` or bundles ES6 modules using `rollup`.

## 5. Reviewing Pull Requests

Pull requests are reviewed using GitHub's built-in review features. Reviews by authorities **SHOULD** be visible in the
pull requests timeline and explicitly signal approval, rejection, or required changes.

A pull request **MAY** be integrated into the repository once it has been approved by the responsible authority.

### 5.1 Review Structure

Reviews **SHOULD** be structured and grouped into questions, suggestions, and change requests. Reviewers are encouraged
to use the following template:

```
Hi @<contributor>,

Thanks a lot for your contribution to <component-name>!

<general-feedback>

Please help me out in answering the following questions:

- [ ] <reference>: <question>?

Please consider the following suggestions. If you decide not to follow then, please indicate your reason(s) briefly.

- [ ] <reference>: <suggestion>

Please implement the following changes:

- [ ] <reference>: <change-request>

Kind regards,
@<member> (as <component-name> <authority>)
```

The placeholders inside the template **MUST** be replaced by the following values, if necessary:

* `<contributor>`: GitHub username of the person who opened the pull request.
* `<general-feedback>`: general feedback on the proposed changes that does not fit into any of the specific categories,
such as background information, context, or relevant state of affairs.
* `<reference>`: a concrete anchor for a review point that allows the contributor to respond precisely and enter a
focused discussion. This is typically a class name, object, method, file name, or a short descriptive identifier.
* `<question>`: a concrete question about the changes that **MUST** be answered before the pull request can be integrated.
* `<suggestion>`: a concrete improvement proposal that requires either implementation or explicit feedback from the
contributor before integration.
* `<change-request>`: a concrete change that **MUST** be implemented by the contributor before the pull request can be
integrated.
* `<member>`: GitHub username of the person reviewing the pull request.
* `<component-name>`: name of the ILIAS component(s) the reviewer has authority over.
* `<authority>`: the authority under which the review is performed (usually code). Sections of the template **MAY** be
omitted if they are not applicable. For trivial or spotless changes, a minimal review or direct approval is acceptable,
as long as the signature clearly indicates who signed off on the changes and for which components.

If no clear authority exists for the affected code, the pull request falls into the shepherd model. In this case, the
signature **MUST** reflect this by using "as shepherd" instead of a component-specific authority.

### 5.2 Review Outcomes

GitHub distinguishes between three review outcomes, which we interpret as follows:

- **Comment**: no required changes or suggestions, but open questions.
- **Request Changes**: at least one suggestion or change request **MUST** be addressed before integration.
- **Approve**: the pull request is ready to be integrated or all review points have been resolved.

## 6. Integrating Pull Requests

Commits from pull requests that are integrated by code-authorities **MUST** adhere to this guideline as well. To
preserve a linear history, there **MUST NOT** be a merge commit created during this process. Hence, all integrations
**MUST** be performed using one of the following strategies.

Please note that the integration also includes the [cherry-picking of commits](#43-cherry-pick), **after the pull
request is integrated into one target branch.**

### 6.1 Rebase and Merge

When individual commits are meaningful and worth preserving separately, you **SHOULD** use this strategy.

1. Ensure the individual commits inside the pull request all adhere to this guideline.
2. Ensure that either all or one of their commit messages contains the [PR appendix](#36-commits-originating-from-pull-requests)
3. Signal your approval visibly inside the pull request, if not done already.
4. Integrate the commits by using GitHubs "Rebase and merge" option. Click the dropdown if this option is not selected.

### 6.2 Squash and Merge

When individual commits are not meaningful and worth preserving separately, you **SHOULD** use this strategy.

1. Signal your approval visibly inside the pull request, if not done already
2. Integrate the commit(s) by using GitHubs "Squash and merge" option. Click the dropdown if this option is not selected.
3. Ensure the final commit message adheres to this guideline ([PR appendix](#36-commits-originating-from-pull-requests)).
