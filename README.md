# WordPress Dashboard To-Do List

Dashboard widget for site-maintenance to-dos: assign users, set priority and due dates, link a related post, and mark complete.

- WordPress.org (after review): https://wordpress.org/plugins/wordpress-dashboard-to-do-list/
- Docs: https://martech.zone/wordpress-plugin-to-do-widget/

Deleting the plugin from wp-admin removes all stored to-dos. Deactivate leaves them in place.

## Local checks

```bash
bash bin/ci_check.sh
```

## WordPress.org

1. Zip the plugin folder (or use `git archive`).
2. Submit at https://wordpress.org/plugins/developers/add/ while logged in as `douglaskarr`.
3. After the review email, add GitHub secrets `SVN_USERNAME` / `SVN_PASSWORD` and publish a GitHub Release whose tag matches `Stable tag`.
