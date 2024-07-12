#!/bin/bash

. scripts/Import/Functions.sh

exit_status=0

if [ -z "${GHRUN}" ]
then
  langfiles=$(git diff --cached --name-only --diff-filter=ACM -- '*.lang')
else
  langfiles=$(get_changed_lang_files)
fi

for file in $langfiles
do
  # Find the duplicate identifiers
  duplicate_identifiers=$(sed -n '/<!-- language file start -->/,$p' $file | awk -F '#:#' '{print tolower($2)}' | sort -f | uniq -d)

  if [[ -n $duplicate_identifiers ]]; then
     echo "In Datei ${file} gibt es Duplikate für folgende Identifier:"

     # Loop through each duplicate identifier
     for duplicate_identifier in $duplicate_identifiers; do
        # Find all lines containing this exact duplicate and print the modules
        IFS=$'\n' lines_with_duplicate=($(sed -n '/<!-- language file start -->/,$p' $file | awk -F '#:#' -v id="$duplicate_identifier" 'tolower($2) == tolower(id){print $1}'))
        # Remove duplicates and create a comma separated list of unique modules
        modules_with_duplicate=$(echo "${lines_with_duplicate[@]}" | tr ' ' '\n' | sort -fu | tr '\n' ',' | sed 's/,$//')
        echo "Doppelter Identifier \"$duplicate_identifier\" in Modulen: $modules_with_duplicate"
     done

     # Mark that duplicates are found
     exit_status=127
  fi
done

exit $exit_status
