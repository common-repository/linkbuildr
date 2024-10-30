=== Linkbuildr ===
Contributors: ftfdevelopment
Donate link: https://ftf.agency/tools/linkbuildr/
Tags: content promotion, outreach automation, outreach, automation
Requires at least: 5.2
Tested up to: 5.3.2
Stable tag: trunk
Requires PHP: 5.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automated content promotion. Share your content with the people who care the most, automatically.

== Description ==

Linkbuildr helps content creators streamline their outreach to people they link to.  

1. SCANS YOUR POST FOR LINKS
When you click publish on a new blog post, Linkbuildr scans your post and identifies all of the website’s your article links to.

1. IDENTIFIES CONTACTS AND INFORMATION
Linkbuildr then check’s your site’s database of contacts to identify any new websites that are not already stored. For new sites it displays a pop-up window for each and suggests a name and email for your to save, edit, or skip.

1. PUSH BUTTON EMAIL OUTREACH
With the simple click of the publish button, Linkbuildr sends an email to each contact it found (and you’ve approved) for hands free content promotion. For more impact you can add custom email templates and set them at the domain level for maximum customization.

1. IMPORT YOUR EXISTING CONTACTS
Import a CSV of your existing contacts with the associated information.

1. SUPPORTS GDPR COMPLIANCE
Allows a user's contacts to unsubscribe from future emails, with a custom unsubscribe landing page, and the ability to delete all of a contact's data.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/linkbuildr` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress

Once installed Linkbuildr will be accessible from the wp-admin.  Management of Contacts & Email Templates is accessible from the Linkbuildr Menu Item in the wp-admin.

== Frequently Asked Questions ==

= How do I send outreach emails through Linkbuildr for Posts that are already Published? =

Updating an existing Published Post will send any Linkbuildr outreach emails that have not already been sent to Linkbuildr Contacts with valid data.
Entering a change and removing it in the Block Editor will allow for a Published Post to be Updated without any changes to the Post.

Additionally switching to a Published Post to Draft and then back to Published will also send the appropriate Linkbuildr emails.

= Can I import bulk contacts into Linkbuildr? =

Yes, using the Contact Importer tool found in the Linkbuildr menu in the WordPress admin.

== Screenshots ==

1. Linkbuildr - Dashboard:  This allows you to see everything in Linkbuildr at a glance.  To the right you have any created Email Templates, to the left you have all of the Contacts.
1. Linkbuildr - Contact Import:  This page allows a user to import a CSV of contacts into Linkbuildr.
1. Linkbuildr - Block Editor:  This shows the Notification that there are Contacts in a post that do not have all the information needed to reach out to.  Additionally this shows the checkbox found on each post that determines if outreach emails are sent on publish.
1. Linkbuildr - Settings: This shows the plugin settings, used to configure Notifications and Unsubscribe settings.

== Changelog ==

= 1.3 =
* Added Ignored Domains list allowing a user to maintain a list of domains that Linkbuildr will ignore any links to
* Added Search to Contacts List
* Updated link parsing to handle Anchor Tag Links and other uses of links
* Added a setting for whether the Send Linkbuildr Emails checkbox is checked or not by default in New Posts.

= 1.2 =
* Added Contact Import from CSV file.

= 1.1 =
* Added Unsubscribe link to emails.
* Added Settings page with settings for in Admin Notifications, and configuration of the Unsubscribe Link in emails.

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 1.3 =
Added an Ignored Domains list, allowing for a list of domains that Linkbuildr will ignore links to.
Added a Search to the Contacts List.
Updated Link/Domain Parsing to better handle edge cases (ie - Anchor Links, Internal Links and Links to domains in the Ignored Domains list)

= 1.2 =
Adds Contact Import, allowing for bulk import of Contacts from CSV.

= 1.1 =
Adds a configurable 'unsubscribe' link to outreach emails sent through Linkbuildr to aid in maintaining GDPR compliance.