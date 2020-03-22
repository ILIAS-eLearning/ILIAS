# ILIAS Offline Capabilities

Being a classic PHP web application, ILIAS relies on the availability of a network connection between the web browser of the user and a web server for almost every user interaction. The goal of this paper is to outline possibilities to deal with slow, unreliable or even unavailable network connections to prevent data loss or to provide certain functionalities offline.

* [Current State](#current-state)
* [Scenarios](#scenarios)
* [Requirements](#requirements)

## Current State

### Temporarily Unavailable Connections

If the connection to the server gets lost during an ILIAS session, a subsequent request e.g. when the user clicks a link, will present a browser standard message stating the loss of the connection. Reloading the page after reconnection will usually load the page. This will even work for POST requests (e.g. if a user submits a form), if the user keeps the browser screen open until the connection is back again.

Data loss may happen e.g. if the user hits the browser back button, closes the browser screen or tab or if the reconnect happens after the user session ended on the server.

If background Ajax calls are involved to save data, e.g. when using the ILIAS page editor, users may not be informed about the loss of the connection directly (they will get a never ending "Saving..." message instead). They have no indication what to do. In this case hitting the "Save" button again after reconnect will save the data, as long as a valid user session still exists. But the probability is high that users will take other actions and lose data.

### Slow Connections

Slow connections may be a result of low bandwidth internet connections, high traffic load on the web server or similar bottlenecks in the network infrastructure. This usually results in decreased responsiveness on the client-side. In extreme cases server timeouts may lead to data loss. But depending on the scenario already a higher response time can be a severe issue for the users, e.g. during an online exam.

### Long Offline Time Periods

In some cases users would like to be able to use some features of the system even if they are offline for a longer time period. E.g. when they go on vacation to a place where they do not have access to internet connectivity or during a trip in a train or on a plane. ILIAS offers some solutions like the SCORM offline player or the HTML export of learning modules. Both these solutions currently suffer from technical issues (using outdated web techniques) or usability issues (lack of progress synchronisation).

## Scenarios

During a requirements workshop in January 2020 we collected a set of use case scenarios that should serve as a base for the requirements gathering. Some of them are already outlined above.

### General Form Handling

A large amount of user data is entered using a standard HTML form / POST request scenario. In the cases of **temporarily unavailable connections** and **slow connections** potential data loss is an issue. The standard browser message ("No Internet...") is not suitable to inform the user on what is happening and what they should do to save their data. The application should provide means to prevent data loss and provide better information of the current connection status for the user.

### Page Editor / Ajax Background Requests

Some special kind of forms, e.g. in the page editor, use ajax calls to save data entered by the user. Similar to the former scenario dealing with **temporarily unavailable connections** and timeouts due to **slow connections** is an issue and the application should prevent data loss and provide better status information.

### E-Exams

E-Exams have a special need for high availability of the application. Any delayed or unclear feedback may distract examinees, both **slow and temporarily unavailable connections** are severe issues with potential legal implications. Some institutions opted to move this scenario into the **long offline time period** category by patching ILIAS to be able to prepare browser clients to run completely offline during the exam. This approach makes it important to be able to reliably synchronise any user entered data back to the server after the exam has been finished.

### Offline Reader

Being able to work through learning content during a **long offline time period** is a typical scenario that has already some support in ILIAS as outlined in the [Current State](#current-state) chapter. A browser based offline player is provided for SCORM content, HTML exports are e.g. available for ILIAS learning modules and glossaries. These are components with no or a low user-to-user interaction. The users mostly work through content and do not interact with other users. Furthermore, the content does not change often, so cloning it temporarily to the client-side is not a huge issue. Similar to the E-Exams scenario data synchronisation once the connection is re-established is an important aspect, e.g. for storing answers given in self-assessment questions.

## Requirements

Common to all scenarios is the goal to provide a reliable user experience independent from the network connection quality. All connection-based problems should be handled in a user friendly and consistent way.

Since the server cannot take care of situations when it is unreachable, the client-side part of the application has to take care of many connection-based problems. It has to handle temporarily cloned content, recognise issues with the connection, provide information to the user and prevent the loss of data.

For ILIAS this means that a substantial part of the solution needs to be implemented in client-side code using Javascript. ILIAS currently suffers from a lack of guidelines for the organisation of complex client-side code.
 
This results in a set of JS coding requirements outlined in chapter [JS Coding Requirements](#js-coding-requirements).

After that the chapter [Service Requirements](#service-requirements) addresses service needs that are originated from the offline scenarios. Central services should provide solutions for these needs to enable higher level components like tests or learning modules to implement their scenarios.

### JS Coding Requirements

#### JS Coding Style

Having a common coding style greatly improves readability of the code and fosters collaboration e.g. via pull request. Chances are high that offline capability implementation will add a decent amount of client-side code to ILIAS, so **having a Coding Style** would be a huge benefit.

#### JS File Naming Conventions

Similar to the coding style a set of **naming conventions** for Javascript files and directory structure should be established.

#### JS Code Pattern Documentation

Developer often face similar problems and often implement similar solutions for these problems. Having a common coding pattern documentation enables a shared understanding of complex structures and supports the maintainability of the code.

In ILIAS there are already some typical coding patterns dealing with modularisation. At least these existing patterns should be **streamlined and documented** before a substantial amount of new code is added.

#### JS API Guidelines

The outlined scenarios for offline use show that different ILIAS components would greatly benefit from a set of basic services that enable these components e.g. to handle and react upon the current network connection state or to tackle communication and synchronisation with the server.

**Guidelines** on how **client-side APIs** should be provided should be outlined before the implementation of these APIs.

#### JS Unit Testing

When adding more dependencies between components on the client by providing client-side APIs, unit tests for these services become more important, since errors in central services may potentially break a larger number of consuming components.

At least a basic **guideline** should support the creation of unit tests in a consistent way throughout all client-side components.

#### JS Packaging and Minification

Currently ILIAS serves a high number (e.g. > 40 in 5.4 repository views) of Javascript files individually to the client. Furthermore, there is no defined practice how to split larger chunks of Javascript code into multiple code files and build a distribution package later.

This results in either large complex Javascript code files or an even higher number of separately delivered files for the client. To increase the efficiency and handle complexity a **guideline on packaging and minification** is needed.

### Service Requirements

#### Connection/Offline State Management

A general connection **service** should manage the connection state and support communication or synchronisation services to reliably hold and resume their processes. It should also support to present general user information if connections are interrupted or re-established.

#### Client/Server Communication

Currently most existing Javascript request in ILIAS to the server are ajax/xhr calls that retrieve HTML snippets for replacements in the current document. If the connection is interrupted, these calls silently fail immediately.

A **service** should support general and common way to transfer data between client and server. The service should take care of connection issues and ensure that all data packages are transferred and processed on the server.

#### Client Server Data Synchronisation / Application State Consistency

Scenarios that support user interactions on the client during a longer offline period, e.g. user entering answers for test questions, need to push these changes back to the server once the connection is re-established.

Conflicts may arise if the same entities are modified in the same time period on the server, too. We need **guidelines and/or services** that help to avoid or to cope with these kind of problems.

#### Client-Side Data/Asset Management

Any presentation for long offline periods will need to manage static assets like HTML, Javascript, CSS or media files. A common storage **service** should support components to deal with assets in client-side storage.

#### Client/Server Implementation Consistency

The need to present content and process user input during longer offline periods (e.g. Offline Reader or E-Exam scenario), can lead to redundant rendering and/or business logic on the server (PHP code) and client (Javascript code). This is currently the case for assessment questions that are processed and evaluated on the server-side during test runs (PHP implementation) and on the client-side (Javascript implementation) when appearing in SCORM or ILIAS learning module content.

Similar cases may arise if input checks in forms (currently processed on the server) are done on the client-side, too, e.g. to improve user experience.

Inconsistencies in these redundant implementations may lead to subtle errors.

We need to evaluate **technical concepts** and options that help to avoid or otherwise cope with these redundancies.


