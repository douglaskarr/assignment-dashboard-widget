=== Assignment Dashboard Widget ===
Contributors: douglaskarr
Tags: dashboard, widget, assignment, tasks, team
Version: 1.0.0
Stable tag: 1.0.0
Tested up to: 7.1
Requires at least: 6.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://dknewmedia.com

Assign dashboard tasks to authors and other users, with priorities, due dates, related posts, and completion tracking.

== Description ==

Assignment Dashboard Widget lets you assign site work to authors, editors, and contributors from a widget on the WordPress dashboard.

Every time someone logs in, their assigned tasks are visible: who owns the work, when it is due, and which post or page it relates to. Mark items complete from the same widget. It is not a project-management platform.

= Features =

* Dashboard widget for Contributors and above.
* Assign tasks to users with the Contributor role or higher (authors, editors, administrators).
* Optional related post, page, or public custom post type. Viewing a task opens that content in a new tab.
* Optional due dates.
* Numeric priority (1 highest through 5 lowest).
* Mark complete from the dashboard. Completed items stay available behind a View Completed filter.
* Admin bar count of open tasks assigned to you.
* Stored as a private custom post type, excluded from search and the public site.
* Deleting the plugin from Plugins → Installed Plugins removes every task and related plugin data. Deactivate keeps your data.

Built by [DK New Media](https://dknewmedia.com/). Documentation: [Martech Zone](https://martech.zone/wordpress-plugin-to-do-widget/).

== Installation ==

1. From Plugins → Add Plugin, search for Assignment Dashboard Widget, or upload the zip.
2. Activate the plugin.
3. Open the Dashboard. The Assignments widget is on that screen.

== Frequently Asked Questions ==

= Who can see the widget? =

Anyone who can edit posts (Contributor and above). Administrators can edit any task. Other roles can edit tasks assigned to them.

= Does uninstall delete my tasks? =

Yes. When you delete the plugin (not merely deactivate it), uninstall removes all assignment posts, their meta, and plugin options. Pages and posts you linked as “related” are not deleted.

= Are assignments public? =

No. They use a private post type, are excluded from search, and have no front-end archive.

== Screenshots ==

1. Assignment widget on the dashboard.

== Changelog ==

= 1.0.0 =
* First WordPress.org release.
* Assign tasks to authors and other users from a dashboard widget.
* Uninstall deletes all plugin data.

== Upgrade Notice ==

= 1.0.0 =
First release. Deleting the plugin removes stored assignments.
