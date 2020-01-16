# ILIAS Offline Capabilities

Being a classic PHP web application, ILIAS relies on the availability of a network connection between the web browser of the user and a web server with almost every user interaction. The goal of this paper is to outline possibilities to deal with slow, unreliable or even non-available network connections to prevent data loss or to provide certain functionalities offline.

## Current State

### Temporarily Unavailable Connections

If the connection to the server gets lost during an ILIAS session, a subsequent request e.g. when the user clicks a link, will present a browser standard message stating the loss of the connection. Reloading the page after reconnection will usually load the page. This will even work for POST requests (e.g. if a user submits a form), if the user keeps the browser screen open until the connection is back again.

Data loss may happen e.g. if the user hits the browser back button, closes the browser screen or tab or if the reconnect happens after the user session ended on the server.

If background Ajax calls are involved to save data, e.g. when using the ILIAS page editor, users may not be informed about the loss of the connection directly (they will get a never ending "Saving..." message instead). They have no indication what to do. In this case hitting the "Save" button again after reconnect will save the data, as long as a valid user session still exists. But the probability is high that users will take other actions and lose data.

### Slow Connections

Slow connections may be a result of low bandwith internet connections, high traffic load on the web server or similar bootlenecks in the network infrastructure. This usually results in decreased responsiveness on the client side. In extreme cases server timeouts may lead to data loss. But depending on the scenario already a higher response time can be a severe issue for the users, e.g. during an online exam.

### Long Offline Time Periods

In some cases users would like to be able to use some features of the system even if they are offline for a longer time period. E.g. when they go on vacation to a place where they do not have access to internet connectivity or during a trip in a train or on a plane. ILIAS offers some solutions like the SCORM offline player or the HTML export of learning modules. Both these solutions currently suffer from differenty technical (using outdated web techniques) or usability issues (lack of progress synchronisation).

## Scenarios

During a requirements workshop in January 2020 we collected a set of use case scenarios that should serve as a base for the requirements gathering. Some of them are already outlined above.

### General Form Handling

A large amount of user data is entered using a standard HTML form / POST request scenario. In the cases of **temporarily unavailable connections** and **slow connections** potential data loss is an issue. Standard browser messages are not suitable to inform the user on what is happening and what they should do. The application should provide means to prevent data loss and better information of the current connection status for the user.

### Page Editor / Ajax Background Requests

Some special kind of forms, e.g. in the page editor, use ajax calls to save data entered by the user. Similar to the former scenario dealing with **temporarily unavailable connections** and timeouts due to **slow connections** is an issue and the application should prevent data loss and provide better status information.

### E-Exams

E-Exams have a special need for high availability of the application. Any delayed or unclear feedback may distract examinees, both **slow and temporarily unavailable connections** are severe issues with potential legal implications. Some institutions opted to move this scenario into the **long offline time period** category by patching ILIAS to be able to prepare browser clients to run completely offline during the exam. This approach makes it important to be able to reliably synchronise any user entered data back to the server after the exam has been finished.

### Offline Reader

Being able to work through learning content during a **long offline time period** is a typical scenario that has already some support in ILIAS as outlined in the [Current State](#current-state) chapter. A browser based offline player is provided for SCORM content, HTML exports are e.g. available for ILIAS learning modules and glossaries. These are components with no or a low user-to-user interaction. The users mostly work through content and do not interact with other users. Additionally the content does not change often, so cloning it temporarily to the client side is not a huge issue. Similar to the E-Exams scenario data synchronisation once the connection is re-established is an important aspect, e.g. for storing answers given in self-assessment questions.

## Requirements

Common to all scenarios is the goal to provide a reliable user experience independent from the network connection quality. This results in handle any connection based problem in a user friendly and common way.

Since the server cannot take care of situations if it is unreachable, the client-side part of the application has to take care of many connection based problems. It has to handle temporatily cloned content, recognise issues with the connection, provide information to the user and prevent the loss of data.

For ILIAS this means that a substantial part of the solution needs to be implemented in client-side code using Javascript. Since ILIAS suffers from a lack of guidelines for the organisation of complex client side code, a number of technical requirements is not directly related to offline scenarios, instead the originate from the need to provide larger client side components.

These two categories of requirements are separated in the two subsequent chapters.

### General Requirements

#### JS Coding Style

Having a common coding style greatly improves readability of the code and fosters collaboration e.g. via pull request. Chances are high that offline capability implementation will add a decent amount of client side code to ILIAS, so having a Coding Style would be a huge benefit.

#### JS File Naming Conventions

Similar to the coding style a set of naming conventions for Javascript files and directory structure should be established.

#### JS Code Pattern Documentation

Developer often face similar problems and often implement similar solutions for these problems. Having a common coding pattern documentation enables a common understanding of complex structures and supports the maintainability of the code.

In ILIAS there are already some typical coding patterns dealing with modularisation. At least these existing patterns should be streamlined documented and before a substantial amount of new code is added.

#### JS API Guidelines

The outlined scenarios for offline use show that different ILIAS components would greatly benefit from a set of basic services that enable these components e.g. to handle and react upon the current network connection state or to tackle communication and synchronisation with the server.

Guidelines on how client side APIs should be provided should be outlined before the implementation of these APIs.

#### JS Unit Testing

#### JS Packaging and Minification

#### Client/Server Communication

#### Client/Server Implementation Consistency


### Requirements of Offline Use

#### Client Side Data/Asset Management

#### Client Server Data Synchronisation

#### Connection/Offline State Management

#### Application State Consistency
