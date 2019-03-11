#!/bin/sh

for CMD in "php" "phpcs"; do
    which "${CMD}" > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        echo "Error: Cannot find executable: ${CMD}"
        exit 1
    fi
done

set -e

cd "$(dirname "$0")/.."

for FILE in $(find . -type f -and \( -name "*.php" -or -name "*.phtml" \) | sort); do
    php -l "${FILE}"

    phpcs --standard=PSR1,PSR2 --exclude="Generic.Files.LineLength" "${FILE}"
done

cd "${OLDPWD}"
