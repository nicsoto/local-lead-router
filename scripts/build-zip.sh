#!/usr/bin/env bash
set -euo pipefail

VERSION="$(grep -E "^[[:space:]]*\\* Version:" local-lead-router/local-lead-router.php | awk '{print $3}')"
OUTPUT_DIR="dist"
OUTPUT_FILE="${OUTPUT_DIR}/local-lead-router-${VERSION}.zip"

mkdir -p "${OUTPUT_DIR}"

if [ -f "${OUTPUT_FILE}" ]; then
	rm "${OUTPUT_FILE}"
fi

zip -r "${OUTPUT_FILE}" local-lead-router \
	-x 'local-lead-router/.DS_Store' \
	-x 'local-lead-router/**/.DS_Store'

printf 'Built %s\n' "${OUTPUT_FILE}"
