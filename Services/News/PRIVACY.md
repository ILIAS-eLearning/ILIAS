# News Service Privacy

This documentation comes with no guarantee of completeness or correctness. Please report any issues (missing or wrong information) in the ILIAS issue tracker.

## Data being stored

- Each **news entry** stores the **user ID** of the original **creator**, a **creation timestamp**, the **user ID** of the last **updater** and a **timestamp of the last update**. _Reason_: This data is presented to other users to identify the author of a news and the date/time of the creation / last update. This is being regarded as important information for learners (e.g. to be able to list news only after a specific date) and for collaboration in general (being able to adress authors of news).
- For **each user** and **each news entry**, ILIAS stores, whether the **news has been presented ("is read")** to the user or not. No timestamp is included in this data. _Reason_: Unread news entries should be visually distinguishable from read entries.


## Data presentation

- Depending on the activation of the feature, ILIAS presents news listings in **various contexts of the repository**, e.g. courses, groups, wikis or forums. This information is usually shown to all users having **Read** permission in this context. 
- Users having **Edit Settings** permission in a context are usually able to edit manual news. Automatic created news (e.g. for new uploaded files) cannot be edited.
- Additionally news are presented in an **personal context in an aggregated form** for users. All news of their favourite repository objects are presented in these views. However only news are presented, that are also accessible in the repository contexts directly (meaning read permission to the context is needed).
- Optionally news can be **configured** as being readable to the **public as an open RSS webfeed**. These feeds include all public news entries of a context and are accessible via an URL without any authentication.

## Data Deletion

- News related to an object are deleted on final object deletion. Users need the **Delete** permission for this action.

## Data Export

- News entries are currently not exportable. If the context objects (e.g. courses) are exported as XML, news data is not included. These object related exports explicitly should not container personal data.
