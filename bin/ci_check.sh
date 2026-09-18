#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

php -l todo-dashboard-widget.php >/dev/null
php -l uninstall.php >/dev/null
for f in includes/*.php; do
	php -l "$f" >/dev/null
done

header_version="$(php -r '
$src = file_get_contents("todo-dashboard-widget.php");
preg_match("/^\s*\*\s*Version:\s*(.+)$/m", $src, $m);
echo trim($m[1]);
')"
readme_version="$(php -r '
$src = file_get_contents("readme.txt");
preg_match("/^Version:\s*(.+)$/m", $src, $m);
echo trim($m[1]);
')"
stable="$(php -r '
$src = file_get_contents("readme.txt");
preg_match("/^Stable tag:\s*(.+)$/m", $src, $m);
echo trim($m[1]);
')"

if [[ "$header_version" != "$readme_version" || "$header_version" != "$stable" ]]; then
	echo "Version mismatch: header=$header_version readme=$readme_version stable=$stable" >&2
	exit 1
fi

echo "ci_check: ok ($header_version)"
echo "Before publishing, run: bash ../bin/run-plugin-check.sh ."
