# To-Do Dashboard Widget

Dashboard widget for site-maintenance to-dos: assign users, set priority and due dates, link a related post, and mark complete.

The public name and slug cannot include the trademarked term “WordPress” (Plugin Check / directory policy). Directory slug: `dashboard-todo-list-widget`.

- Docs: https://martech.zone/wordpress-plugin-to-do-widget/

Deleting the plugin from wp-admin removes all stored to-dos. Deactivate leaves them in place.

## Local checks

```bash
bash bin/ci_check.sh
```

**Required before every WordPress.org submission or tag:** [Plugin Check (PCP)](https://wordpress.org/plugins/plugin-check/) must report no errors.

```bash
bash ../bin/run-plugin-check.sh .
```

CI runs the same checks via [wordpress/plugin-check-action](https://github.com/WordPress/plugin-check-action).

## WordPress.org

1. Pass Plugin Check (no errors).
2. Zip the plugin folder (or use `git archive`).
3. Submit the zip at https://wordpress.org/plugins/developers/add/ while logged in as `douglaskarr` (slug `dashboard-todo-list-widget`).
4. After the review email, publish a GitHub Release whose tag matches `Stable tag`.
