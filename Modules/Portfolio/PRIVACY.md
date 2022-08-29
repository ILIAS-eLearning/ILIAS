# Portfolio Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Portfolio Module component employs the following services, please consult the respective privacy.mds
    - The **Object** service stores the account which created the
      object as it's owner and creation and update timestamps for the
      object.
    - [COPage](../../Services/COPage/PRIVACY.md)
    - The **Blog** module allows to create blog postings shared with others. These blogs can be integrated into a portfolio.
    - The **User** service stores personal data like name, address and birthday.
    - The **Learning History** service collects data related to learning activites like received badges or successful completions of courses.
    - The **Membership** service stores information on memberships, e.g. in courses and groups.
    - The **Calendar** service stores information on (personal) appointments and consultation hours of tutors.
    - The **Competence** service stores information on competence evaluations and assessments.
    - The **Notes/Comments** service stores data on comments attached to portfolio or blog pages.

## General Information

The main purpose of portfolios is to present information about its creator to others. So almost all data entered by the creator of a portfolio is personal data being stored and presented.

## Configuration

**Global**

- The general **feature is activated** in the portfolio administration (Administration > Personal Workspace > Portfolio).
- The global portfolio administration also includes a setting that enables to **share** the current **courses of a user** as part of the portfolio.
- It is possible to activate a feature that enables users to **publish** their portfolios to the **outside web**, without any authentication. The availability of this option is controlled by the setting "Anonymous Access > Enable User Content Publishing" under "Administration > System Settings and Maintenance > General Settings > Basic Settings".

**Portfolio**

- The author of a portfolio controls its content and how it is shared to others. This is done in the **Share** tab of a portfolio. Portfolios can be presented to single users, all members of a group, all members of a course, all registered users or even externally to the web (see global configuration).
- The portfolio settings allow to activate public comments service. These comments are attached to portfolio pages.

## Data being stored

Single portfolio pages are stored using the [COPage](../../Services/COPage/PRIVACY.md) service. The author may include any personal content in these pages. The pages are not structured with any personal data related scheme (like e.g. the user service storing birthday, name or address information).

The Blog module is being used to store blog postings. Similar almost everything included in a portfolio is data being stored by the integrated services.

The portfolio itself does not store any additional personal data.

## Data being presented

Beside the personal information that the author puts directly into portfolio or blog pages, it is possible to embed information of other services that present "live" data in the portfolio:

- Personal Profile data of the user.
- The learning history of the user, including received badges, learning progress status, certificates and competences.
- The current course memberships of the user.
- The consultation hours of the user.

If activated public comments will be listed under each portfolio page.

All data of the portfolio (incl. comments) is visible to all users that are defined in the "Share" tab of the portfolio (see Configuration above).

## Data being deleted

- The author can remove any information from the portfolio or blog pages anytime. This will remove it from the presentation to other users. However the information will be part of the history of that particular page. The history is not presented to other users.
- Deleting a page will delete any data stored directly within the page, but not the original data of other services (e.g. learning history data or consultation hours).
- Deleting a portfolio will delete all pages included. Embedded data of other services and blogs will not be deleted with the portfolios. Blogs need to be deleted separately by the user.

## Data being exported

- Portfolios can be exported as a zipped folder of HTML files.
- A print view can be used to convert a portfolio to PDF by the browser.
