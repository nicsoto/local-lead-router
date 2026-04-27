#!/usr/bin/env bash
set -euo pipefail

find local-lead-router -name '*.php' -print0 | xargs -0 -n1 php -l
node --check local-lead-router/admin/js/lead-router-admin.js
