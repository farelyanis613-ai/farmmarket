#!/bin/bash
# Utility: remove sensitive files from git tracking (run from repo root)
set -e
FILES=(.env cookies.txt response.html lint_results.txt database.sql composer.lock)
for f in "${FILES[@]}"; do
  if [ -e "$f" ]; then
    git rm --cached --ignore-unmatch "$f" || true
    echo "Removed $f from git index (if it was tracked)."
  fi
done

echo "Done. Don't forget to add .gitignore and commit the change." 
