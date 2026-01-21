# Git Guidelines

This document defines the git practices we live by inside the ILIAS community when working on this repository. Its
primary purpose is to ensure transparency, consistency, and clear responsibility when contributing code, reviewing
changes, and integrating them into maintained branches. It also serves as a reference for [authorities who sign off on
code changes](./maintenance.md#authorities).

## 1. Core Principles

All rules and recommendations in this document follow a small set of principles:

- **Transparency**: Every code change must be traceable to a concrete reason (issue, feature, or release) and to the
  discussion that led to its acceptance.
- **Atomicity**: Each commit must leave the repository in a valid, buildable, and deployable state.
- **Authority**: Changes are reviewed and approved by explicitly defined authorities, and this approval must be visible.
- **Consistency**: Changes must be applied to all maintained versions of ILIAS by all persons in a controlled and
  reproducible way.

## 2. Commit Types and Semantics

We distinguish between three kinds of commits. The commit type is mandatory and is a first step to set expectations
about its changes.

- **Bugfix**: Addresses a concrete issue reported in the issue tracker.
- **Feature**: Introduces, changes, or removes functionality.
- **Release**: Updates version numbers and dump files.

Every commit must only be associated to exactly one of these types. Mixing these concerns is not possible.

## 3. Commit Message Conventions

Commit messages must follow a strict structure to ensure readability and transparency. All authorities who sign off on
code changes must ensure they comply with it.

### 3.1 Bugfix

```
[FIX] #<issue-nr> <component>: <summary>

<description>
```

* `<issue-nr>`: the issue number being addressed.
* `<component>`: the ILIAS component responsible for the affected files. If multiple components are involved, list them
  comma-separated. Responsibility is determined by ownership, not by syntactic location (e.g. `Setup` owns
  `./cli/setup.php`, `Language` owns `./lang`).
* `<summary>`: a concise, factual description of the change.
* `<description>` (optional): further details, typically an unordered list.

### 3.2 Feature

```
[FEATURE] <component>: <summary>

<description>
```

* `<component>`: the component implementing the feature. Multiple components must be listed comma-separated. If only a
  usage is adapted, this component may be omitted.
* `<summary>`: a concise description of the functional change.
* `<description>`: a detailed explanation of the implementation or behaviour, ideally linking the feature wiki entry.

### 3.3 Release

```
[RELEASE] <version>
```

* `<version>`: the release version, prefixed with `v`, mirroring the associated git tag.

## 3.4 Components and Component Substitutes

Most commits can be attributed to a concrete ILIAS component. For other locations inside the repository without a clear
component associated with it, the following substitutes must be used:

* `Documentation`: changes to `./docs` and other top-level documentation files (e.g. `README.md`, `LICENSE`).
* `CI`: changes to automation, QA tooling, scripts, or related configuration (e.g. `./scripts`, `./.github`,
  `./captainhook.json`).
* `Dependency`: changes to `composer` or `npm` related files. These files must not be edited manually and usually change
  only due to dependency updates.
* `Authority`: changes to `./docs/development/maintenance.md` that affect authority definitions only.

_If none of these substitutes match the changes of the commit, this document may be extended and the component or
substitute may be omitted for the time being._

## 3.5 Commits Originating from Pull Requests

Commits that originate from pull requests must preserve a reference to the GitHub discussion. This ensures that design
decisions and review outcomes remain easily traceable and accessible.

So when integrating a pull request, the final commit message must be amended as follows:

```
<commit-message> (#<pr-number>)
```

* `<commit-message>`: a commit message that complies to the conventions above.
* `<pr-number>`: the pull request number as shown in GitHub.

**This appendix is mandatory for all PR-based commits.** GitHub will automatically create a link to the pull request and
mention cherry-picked commits with the same appendix inside the pull requests timeline, leaving a transparent overview.

## 6. Reviewing Pull Requests

Pull requests are reviewed using GitHub's built-in review features. Reviews by authorities must be visible in the PR
timeline and explicitly signal approval, rejection, or required changes.

Every pull request that is integrated into the repository should, ideally, be explicitly approved by the responsible
authority.

### 6.1 Review Structure

Reviews should be structured and grouped into questions, suggestions, and change requests. Reviewers are encouraged to
use the following template:

```markdown
Hi @<contributor>,

Thx a lot for your contribution to <component-name>!

<general-feedback>

About the concrete changes, please answer the following questions:

- [ ] <reference>: <question>?

Please consider the following suggestions. You do not need to follow them, but please indicate briefly why you prefer to
do otherwise:

- [ ] <reference>: <suggestion>

Please implement the following changes:

- [ ] <reference>: <change-request>

Kind regards,
@<member> (as <component-name> <authority>)
```

The placeholders inside the template must be replaced by the following values, if necessary:

* `<contributor>`: GitHub username of the person who opened the pull request.
* `<general-feedback>`: general feedback on the proposed changes that does not fit into any of the specific categories,
  such as background information, context, or relevant state of affairs.
* `<reference>`: a concrete anchor for a review point that allows the contributor to respond precisely and enter a focused
  discussion. This is typically a class name, object, method, file name, or a short descriptive identifier.
  class-, object- or filename, or other few words that can be quickly referenced.
* `<question>`: a concrete question about the changes that must be answered before the pull request can be integrated.
* `<suggestion>`: a concrete improvement proposal that requires either implementation or explicit feedback from the
  contributor before integration.
* `<change-request>`: a concrete change that must be implemented by the contributor before the pull request can be
  integrated.
* `<member>`: GitHub username of the person reviewing the pull request.
* `<component-name>`: name of the ILIAS component(s) the reviewer has authority over.
* `<authority>`: tthe authority under which the review is performed (usually code).

Sections of the template may be omitted if they are not applicable. For trivial or spotless changes, a minimal review or
direct approval is acceptable, as long as the signature clearly indicates who signed off on the changes and for which
components.

If no clear authority exists for the affected code, the pull request falls into the shepherd model. In this case, the
signature must reflect this by using "as shepherd" instead of a component-specific authority.

### 6.2 Review Outcomes

GitHub distinguishes between three review outcomes, which we interpret as follows:

- **Comment**: no required changes or suggestions, but open questions.
- **Request Changes**: at least one suggestion or change request must be addressed before integration.
- **Approve**: the pull request is ready to be integrated or all review points have been resolved.

## 7. Integrating Pull Requests

All pull requests must be integrated using the **squash and merge** option. This ensures a clean history and enforces
atomic commits to the repository. Squashing the commits also gives us the opportunity to amend the commit message, to
make sure they all comply with our conventions. **For traceability its important to add the
[PR appendix](#35-commits-originating-from-pull-requests) during this step**.

As a general rule of thumb, the following rules apply:

* Prefer a single commit per pull request.
* Multiple commits are acceptable only if they represent clearly separable concerns. However, this will prevent adding
  the PR appendix to the commit message, so an according change must be requested by the contributor.
* The final commit message(s) must comply with the defined conventions.
* The commit must include a PR reference appendix.
* Integration must be performed by an authority responsible for the affected component(s). Integration also includes
  the integration into other relevant branches of the repository.

### 7.1 Recommended Integration Procedure

1. Review the pull request.
2. Inspect the commit history and ensure all commits can be squashed.
3. Determine the target branches for integration.
4. Perform a squash merge and edit the commit message:
    * ensure compliance with commit message conventions;
    * append the PR number.
5. Finalise the merge.
6. Cherry-pick the resulting commit into all relevant maintained branches.

## 8. Cherry-picking Commits

To apply changes across different major versions of ILIAS, we use `git cherry-pick`. This is all straightforward, except
for changes that affect compiled assets (e.g. CSS or bundled JavaScript), that are tracked inside the repository.

When cherry-picking commits that affect such resources:

* Apply the cherry-pick as far as possible, but there may be conflicts.
* Rebuild affected assets using the appropriate tools (e.g. `sass`, `npx rollup`). This should resolve conflicts if
  there were any.
* Rebuild assets even if no conflicts occur, to verify that no faulty changes are introduced.
* When re-building assets results in changes, amend the commit using `git commit --amend`, preserving the original
  commit message and all possible cross-references.

This process ensures consistency across branches while keeping history, intent, and discussions intact.
