#!/bin/bash

# Fix common errors:
# Referencing undefined variables (which default to "")
# Ignoring failing commands
set -o nounset
set -o errexit
set -o pipefail
# Note this breaks passing a variable as args to a command
IFS=$'\n\t'

echo "================================================"
echo "Beginning of log:"
head /var/log/pacman.log
echo "================================================"
echo "Number of lines in log:"
wc -l /var/log/pacman.log
echo "================================================"
echo "Number of lines grouped by ^20xx:"
for i in $(seq -w 9 19); do RESULT=$(grep -c "^\[20$i" /var/log/pacman.log); echo "20$i: $RESULT"; done
echo "================================================"
echo "Currently installed number of packages:"
pacman -Q | wc -l
