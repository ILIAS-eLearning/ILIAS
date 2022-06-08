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

## General Information

The main purpose of portfolios is to present information about its creator to others. So almost all data entered by the creator of a portfolio is personal data being stored and presented.

## Configuration

**Global**

- The general **feature is activated** in the portfolio administration (Administration > Personal Workspace > Portfolio).
- The global portfolio administration also includes a setting that enables to **share** the current **courses of a user** as part of the portfolio.
- It is possible to activate a feature that enables users to **publish** their portfolios to the **outside web**, without any authentication. The availability of this option is controlled by the setting "Anonymous Access > Enable User Content Publishing" under "Administration > System Settings and Maintenance > General Settings > Basic Settings".

**Portfolio**

- The author of a portfolio controls its content and how it is shared to others. This is done in the **Share** tab of a portfolio. Portfolios can be presented to single users, all members of a group, all members of a course, all registered users or even externally to the web (see global configuration).

## Data being stored

Single portfolio pages are stored using the [COPage](../../Services/COPage/PRIVACY.md) service. The author may include any personal content in these pages. The pages are not structured with any personal data related scheme (like e.g. the user service storing birthday, name or address information).

The Blog module is being used to store blog postings. Similar almost everything included in a portfolio is data being stored by the integrated services.

The portfolio itself does not store any additional personal data.

## Data being presented

...

## Data being deleted

...

## Data being exported

...