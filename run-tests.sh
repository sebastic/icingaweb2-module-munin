#!/bin/sh

for CMD in "php" "phpcs" "shellcheck"; do
    if ! command -v "${CMD}" > /dev/null 2>&1; then
        echo "Error: Cannot find executable: ${CMD}"
        exit 1
    fi
done

ERRORS=0

cd "$(dirname "$0")" || exit 1

for FILE in $(find . -type f -and \( -name "*.php" -or -name "*.phtml" \) | sort); do
    php -l "${FILE}"
    rc=$?
    if [ $rc != 0 ]; then
        ERRORS=$((ERRORS + rc))
    fi

    phpcs --standard=PSR1,PSR2 --exclude="Generic.Files.LineLength" "${FILE}"
    rc=$?
    if [ $rc != 0 ]; then
        ERRORS=$((ERRORS + rc))
    fi
done

for FILE in $(find . -type f -and -name "*.sh" | sort); do
    shellcheck "${FILE}"
    rc=$?
    if [ $rc != 0 ]; then
        ERRORS=$((ERRORS + rc))
    fi
done

cd "${OLDPWD}" || exit 1

exit "${ERRORS}"
