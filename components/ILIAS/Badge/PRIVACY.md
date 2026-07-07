# Badge Privacy

>  **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).**


## General Information

The Badge Service allows the awarding of digital badges to users for various achievements. Badges can be awarded automatically (triggered by events like course completion) or manually by persons with appropriate permissions. Users can view and manage their received badges in their personal profile.

The badge backpack export feature (Mozilla Open Badges) is currently disabled.

## Integrated Services

The Badge component employs the following services (please consult their respective PRIVACY.md files):

- **[User](../User/PRIVACY.md)**: The Badge service stores references to user accounts and triggers badge evaluation based on user events.
- **[Tracking](../Tracking/PRIVACY.md)**: Badge awards can be triggered by learning progress status changes.
- **[Mail](../Mail/PRIVACY.md)**: Email notifications are sent to users when badges are awarded.
- **[AccessControl](../AccessControl/PRIVACY.md)**: Permissions control who can manage badges and view badge assignments.
- **[Notification](../Notification/PRIVACY.md)**: On-screen notifications are sent when badges are awarded.

## Data being stored

- **Badge Assignment**: When a badge is assigned to a user, the following data is stored in the database table `badge_user_badge`:
  - The user ID of the badge recipient
  - The timestamp when the badge was awarded
  - The user ID of the person who manually awarded the badge, if applicable
  - A position value for ordering badges in the user's profile display

- **User Preferences**: The following user preferences are stored via the user preference system:
  - `badge_last_checked`: Timestamp of when the user last viewed their badges
  - `badge_mozilla_bp`: Optional email address for badge backpack integration

## Data being presented

- **Personal Badge View**: Each user can view their own badges in their personal profile. This includes badge title, description, image, award date, and the name of the awarding object.

- **Badge Management View**: Persons with "write" permission on a repository object (course, group, etc.) can access the badge management interface. This view presents:
  - Names (firstname, lastname) of users who have been awarded badges
  - Login names of badge recipients
  - The date when badges were issued
  - The badge type and title

- **Administration View**: Persons with "visible,read" permission on the Badge Administration can view all object badges and their assignments across the system. This includes:
  - List of users with badge assignments, showing name, login, badge type, title, and issue date
  - The parent object where the badge was awarded

- **Badge Award Information**: When viewing a badge in the tile view, the name of the awarding object is displayed to the badge recipient.

## Data being deleted

- **User Deletion**: When a user account is deleted from the system, all badge assignments for that user are automatically deleted.

- **Badge Deletion**: When a badge definition is deleted, all assignments of that badge to users are automatically deleted.

- **Manual Removal**: Persons with "write" permission on a repository object can manually remove badge assignments from individual users via the "Users" tab in badge management.

- **Object Deletion**: When a repository object containing badges is deleted from trash, the badge definitions and their assignments are deleted. Note: the Badge service does not implement `deleteByParentId` cleanup automatically; badge definitions may remain in the database referencing deleted objects.

## Data being exported

- **No Export of Assignments**: There is no built-in export functionality for badge assignment data.

- **Backpack Export (Disabled)**: The Open Badges backpack export feature is currently disabled. When enabled, it generates static JSON files according to the Open Badges specification containing badge class information and user-specific assertion files.

- **XML Export**: Badge definitions can be exported as part of repository object exports, but this does not include user assignment data or other personal information.
