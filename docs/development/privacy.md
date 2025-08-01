# Privacy Checklist for ILIAS Development

Start with a quick self-check. Before diving into specific questions, take a moment to reflect on your approach:

- [ ] Have I considered all data protection-relevant aspects of this feature?
- [ ] Could I clearly explain my solution to a data protection officer?
- [ ] Would I feel comfortable using this feature on a platform that processes my own data?

If you can confidently answer “yes” to all of the above – great! If not, the checklist below will help identify areas where your implementation can become more privacy-friendly.

### 1. Data Collection
- [ ] Are personal data being processed? If so, which data exactly?
- [ ] Is the processing technically necessary – or just “nice to have”?
- [ ] Is there a way to reduce data usage (smaller scope, shorter storage duration)?

### 2. Defaults & Control
- [ ] Is the most privacy-friendly setting used by default (Privacy by Default)?
- [ ] Can admins and, if applicable, users adjust the settings?
- [ ] Are there tooltips or notices that explain data usage (transparency)?

### 3. Access & Roles
- [ ] Is access to personal data role-based and restricted?
- [ ] Is there protection against unintended disclosure (e.g. masking in logs)?
- [ ] Are sensitive data only displayed or stored when necessary?

### 4. Deletion & Storage Limitation
- [ ] Can the data be fully deleted if required?
- [ ] When a user or course is deleted, are all associated data reliably removed?
- [ ] Is there a feature for automatic or scheduled deletion?

### 5. Export & Access Requests
- [ ] Can personal data be exported upon request?
- [ ] Are all processed data transparently listed in the technical documentation?
- [ ] Is there a way to provide users with automated access to their data?

### 6. Logging & Analytics
- [ ] Are personal data captured in logs? If so: for how long, and why?
- [ ] Can logs be anonymized or pseudonymized?
- [ ] Are analytics features implemented in a privacy-friendly way?

### 7. Third Parties & Interfaces
- [ ] Are data transmitted to external services (API, plugins)?
- [ ] Is this documented and made transparent to administrators?
- [ ] Is it ensured that no third-party integrations are active without consent?

### 8. Documentation & Communication
- [ ] Does the feature/plugin documentation include a ‘Privacy’ section?
- [ ] Is it clearly explained what data is processed and how?
- [ ] Are there instructions for admins on configuration and privacy-friendly usage?
