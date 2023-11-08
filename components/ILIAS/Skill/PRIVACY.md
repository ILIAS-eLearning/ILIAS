# Skill (Competence) Service Privacy

Disclaimer: This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information

The details on **how competence data is presented in other components** (survey,
course, portfolios, staff, ...) should be **documented in the privacy documentation
of these components**.

## Data being stored

- **Personal competences** selected by users (User ID / Skill ID).
- **Competence profile assignments**. Competence management administrators can
  assign competence profiles to users directly or via roles and organisational
  units. Course administrators can assign competence profiles to the local member
  role (Profile ID + User ID/Role ID/OrgUnit ID).
- Documents to prove personal skill level achievements: Users can **assign workspace
  files to single skill levels**. The documents are organised in the workspace
  service. The skill service stores the reference "user/document/skill level".
- **Competence assessment/achievements** are aquired either by self evaluation,
  evaluation of others (e.g. 360° surveys) or measured by tools like tests. For
  all these cases the service stores user ID, competence level ID, timestamp of
  achievement, triggering object (e.g. test ID) and level of fulfillment (e.g.
  60% of level 3).

## Data being presented

- **Administrators** of the competence management system have an overview on the
  **assignment** of **users to competence profiles**.
- **Administrators/Tutors of repository objects** (e.g. course, survey, test) that
  make use of the competence service can assign competences and profiles to these
  objects. They can configure how competence levels will be achieved by measurement
  or manual assignment. Usually access is controlled by "**write/edit settings**"
  or "**edit learning progress**" permissions. The repository objects can provide
  views that show the **participating users** (e.g. members or survey participants)
  and **their competence level achievements**, also **compared to assigned profiles**.
- The **users themselves get an overview on their assigned profiles and achieved
  competence levels**. These views allow to perform self evaluations and assign
  materials of proof to single competence levels. In the context of repository
  objects they get overviews of achieved competences within these objects and
  locally assigned profiles.
- **Users may publish their personal competence achievements** in **portfolios**.
  The portfolio service controls the access to the portfolios including the competence
  data.
- The **staff (MyStaff) service** presents competence achievement data for superiors,
  too.

## Data being deleted

- Personal selected competences can be deselected by the user anytime. They are
  also deleted on user deletion.
- Personally assigned workspace documents can be removed by the user anytime.
- Assignments to competence profiles are deleted when either the assignment is
  removed by tutors/admins or the profile or the user is deleted.
- Competence achievement data is deleted on user deletion.
