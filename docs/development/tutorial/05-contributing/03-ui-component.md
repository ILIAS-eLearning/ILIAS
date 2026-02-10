# How to extend or create an UI component?

## What is This About?

As someone who wants to contribute to the ILIAS UI framework or Kitchen Sink, you need to follow some specific rules in
order to make sure the framework behaves in a uniform and predictable way across all UI components. Since a lot of code
relies on the framework, there are also certain processes which need to be followed when introducing a new UI component
and/or modify an existing one.

This article will give you an overview of these rules and processes and guide you through them.

## Why do I Need This in ILIAS?

Contributing to the ILIAS UI framework and Kitchen Sink is important, so we can continuously improve it and ultimately
build entire pages with UI components. This guide is essential for developers aiming to contribute new components to
ILIAS. It ensures a structured and efficient process while maintaining consistency and quality in the ILIAS codebase.

## How to Proceed?

### The UI-Clinic

Before extending or creating any UI component, we strongly recommend to consider reaching out to the UI-Clinic first.
The UI-Clinic offers on-demand consultation hours, providing assistance and guidance from UI/UX/A11y experts, with whom
you can discuss your concept and/or issue. You can find out more about this
group [in our forum](https://docu.ilias.de/goto_docu_grp_12155.html). The procedure to enter such a consultation is
simple:

- Enter a request in the data-collection: [UI Clinic Requests](https://docu.ilias.de/goto_docu_dcl_8186_166.html)
- Think about questions you would like to ask/discuss.
- Come to the next consultation hour.

### Introducing a new UI component

New UI components are introduced in the UI framework and the Kitchen Sink simultaneously, to maintain correspondence
between them. To introduce a component you need to create an entry in the Kitchen Sink, which passes through three
stages:

- **To be revised**: The entry is still being worked on. Just use a local copy or a fork of the ILIAS repository and try
  out what ever you want on a new branch.
- **Proposed**: The entry has been revised and is proposed to the Jour Fixe, but has not yet been decided upon. To enter
  this state, create a pull request against on GitHub against the latest trunk (branch), containing your proposed
  component and present it on the Jour Fixe. You need to provide a (mostly) complete definition of the component but an
  implementation is not required at this point. Your will have better chances if you also bring some visual
  representation of your new component, you may use the ILIAS edge branch for that.
- **Accepted**: The entry has been accepted by the JF. This, as always, might need some iterations on the component.

These stages are represented by using functionality of git and GitHub. After acceptance, the new entry is part of the
Kitchen Sink as well as part of the source code in the trunk. How this entry is composed and published for JourFixe will
be covered in the next section.

### Implementing and extending an UI component

A full step-by-step guide on how to implement and/or extend an UI component with the most relevant implementation
details can be found
in [the frameworks README file](../../../../../components/ILIAS/UI/README.md#Implementing-Elements-in-the-Framework).

## What do I Need to Watch Out For? (Dos & Dont's)

- Refer to the UI-Clinic to discuss UI/UX concepts in advance.
- Ensure adherence to the defined template for component descriptions.
- Maintain immutability in UI components.
- Provide examples and mockups to facilitate discussions during the Jour Fixe.
- Link relevant bugfixes or feature requests to provide context and transparency regarding the necessity of changes.
- Formulate relevant test cases on TestRail.