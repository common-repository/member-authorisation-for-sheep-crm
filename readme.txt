=== Member authorisation for Sheep CRM ===
Contributors: tallprojects
Tags: membership, sheep, sheep CRM, sheepCRM, crm, database, authorisation, member, members, association
Requires at least: 4.4
Requires PHP: 5.3
Tested up to: 5.4.1
Stable tag: trunk

Grants/revokes a specified WordPress role for users at login based upon their membership status in SheepCRM.

== Description ==

Grant (and revoke) specified roles at user login, based on their membership status in a [SheepCRM membership database](http://getsheep.co.uk?ref=wp-member-auth-plugin).

These roles can then be used for controlling access to member-only content.

User accounts are created and managed in WordPress. These are separate from SheepCRM. The WordPress user's email address is used in a query against the people records in Sheep on login. The specified member role is granted (and non-member role revoked) if the email address matches a person in Sheep who has an active membership.

Conversely, if the user does not have an active membership their member role is removed and the non-member role granted.

Note that this plugin does not use Sheep user accounts for authentication. Please [contact Tall Projects](https://www.tallprojects.co.uk?ref=wp-member-auth-plugin) if you require additional functionality.

= Fault tolerant =

No changes to a user's roles will be made in the (unlikely) event of any issues contacting Sheep or error responses returned. Users are still able to login but their roles won't be updated.

= Administrator bypass =

Users with the `administrator` role bypass this plugin. They are not checked against Sheep, nor are their roles changed.

This is an important and deliberate design consideration. It ensures your WordPress admin user(s) don't inadvertently gain or lose roles, which could cause issues managing your site.



== Screenshots ==

1. The settings screen



== Installation ==

1. Create a [SheepCRM API key](https://intercom.help/sheepcrm/automation/creating-a-sheep-api-key).
1. Install and activate the plugin.
1. Go to Settings -> Sheep member authorisation.
1. Enter your Sheep flock name (client account identifier), Sheep API key and choose the roles you wish to grant/revoke on login.


== Frequently Asked Questions ==
 
= What happens if the user's email address isn't found in SheepCRM? =
 
Nothing. The user will continue to log in as normal. Their account and roles are not altered.
 
= I need online joining / renewals / member self-service... What can I do? =
 
This plugin is a simple way to check if your existing WordPress users have an active membership in Sheep.

Deeper integrations between WordPress and SheepCRM are available. These include:

- Online joining and renewals
- Member self-service, including managing linked members on organisational memberships
- Using Sheep's user accounts for authentication, automatically creating/updating WordPress user accounts for them as needed
- Event registration
- Form data capture to Sheep journal records

Please [contact Tall Projects](https://www.tallprojects.co.uk?ref=wp-member-auth-plugin) for more details.


== Credits ==

Developed by [Tall Projects](https://www.tallprojects.co.uk?ref=wp-member-auth-plugin). Kindly supported by the [Professional Speaking Association](https://www.thepsa.co.uk).

== Changelog ==

= 1.1 - 2020-05-20

* Performance enhancement: query is now made against the contact directory rather than person self-service API.
* Sheep query now uses Sheep's case-insensitive matching operator.

= 1.0.5 - 2019-06-17

* New option to control timeout value for requests to Sheep.

= 1.0.4 - 2018-09-06

* Adjustment to Sheep email query. Now handles matches where a type label has been applied to the email address stored in Sheep.

= 1.0.3 - 2018-06-07 =

* Initial public release

