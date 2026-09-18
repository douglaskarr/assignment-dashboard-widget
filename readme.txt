=== WordPress Dashboard To-Do List ===
Contributors: douglaskarr
Tags: dashboard, to-do, todo, tasks, widget
Version: 1.0.0
Stable tag: 1.0.0
Tested up to: 7.1.1
Requires at least: 6.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://dknewmedia.com

A dashboard to-do list with assignments, priorities, due dates, related posts, and completion tracking.

== Description ==

WordPress Dashboard To-Do List keeps site maintenance tasks on the WordPress dashboard, where the work happens.

Every time you log in, outstanding to-dos are visible, prioritized, and actionable. It is not a project-management platform. It is a focused dashboard reminder system.

= Features =

* Dashboard widget for Contributors and above.
* Assign to-dos to users with the Contributor role or higher.
* Optional related post, page, or public custom post type, with a search field. Viewing a to-do opens that content in a new tab.
* Optional due dates.
* Numeric priority (1 highest through 5 lowest).
* Mark complete from the dashboard. Completed items stay available behind a View Completed filter.
* Admin bar count of open to-dos assigned to you.
* Stored as a private custom post type, excluded from search and the public site.
* Deleting the plugin from Plugins → Installed Plugins removes every to-do, meta field, and related option. Deactivate keeps your data.

Built by [DK New Media](https://dknewmedia.com/). Documentation: [Martech Zone](https://martech.zone/wordpress-plugin-to-do-widget/).

== Installation ==

1. From Plugins → Add Plugin, search for WordPress Dashboard To-Do List, or upload the zip.
2. Activate the plugin.
3. Open the Dashboard. The To-Do List widget is on that screen.

== Frequently Asked Questions ==

= Who can see the widget? =

Anyone who can edit posts (Contributor and above). Administrators can edit any to-do. Other roles can edit to-dos assigned to them.

= Does uninstall delete my to-dos? =

Yes. When you delete the plugin (not merely deactivate it), uninstall removes all to-do posts, their meta, and plugin options. Pages and posts you linked as “related” are not deleted.

= Are to-dos public? =

No. They use a private post type, are excluded from search, and have no front-end archive.

== Screenshots ==

1. Dashboard to-do list widget.

== Changelog ==

= 1.0.0 =
* First WordPress.org release (continues the private 2.2.0 codebase).
* GPL distribution; no paid license.
* Uninstall deletes all plugin data.
* Capability checks on AJAX and the admin bar.
* Related-content search limited to public post types.

== Upgrade Notice ==

= 1.0.0 =
Free WordPress.org release. Deleting the plugin removes stored to-dos.
