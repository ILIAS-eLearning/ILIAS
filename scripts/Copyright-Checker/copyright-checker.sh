#!/bin/bash

# This file is part of ILIAS, a powerful learning management system
# published by ILIAS open source e-Learning e.V.
#
# ILIAS is licensed with the GPL-3.0,
# see https://www.gnu.org/licenses/gpl-3.0.en.html
# You should have received a copy of said license along with the
# source code, too.
#
# If this is not the case or you just want to try ILIAS, you'll find
# us at:
# https://www.ilias.de
# https://github.com/ILIAS-eLearning

# This script will be used to check files for the official ILIAS
# copyright license header (the content of which is also above).
#
# @author Thibeau Fuhrer <thibeau@sr.solutions>
# @version 1.0.0

# the ${IFS} is a constant used by bash to explode output into arrays.
# this assignment changes the default-behaviour to only split strings
# into arrays on linebreaks instead of (almost) all whitespace chars.
IFS=$'\n'

# the ${COPYRIGHT_LINES} constant holds an array of lines, excluding
# the comment-ending deliberately.
COPYRIGHT_LINES=(
  "/**"
  " * This file is part of ILIAS, a powerful learning management system"
  " * published by ILIAS open source e-Learning e.V."
  " *"
  " * ILIAS is licensed with the GPL-3.0,"
  " * see https://www.gnu.org/licenses/gpl-3.0.en.html"
  " * You should have received a copy of said license along with the"
  " * source code, too."
  " *"
  " * If this is not the case or you just want to try ILIAS, you'll find"
  " * us at:"
  " * https://www.ilias.de"
  " * https://github.com/ILIAS-eLearning"
)

# DESC: checks if the given file contains the official ILIAS copyright
#       license header at the beginning of its content.
#
#       NOTE that this function does not check the copyright-ending
#       deliberately, because developers have been using multiple
#       different variations. This also leaves the option to maybe
#       add some further document-level comment beneath.
#
# ARGS: [<string>] file to check
function is_copyright_valid() {
  local file="${1}"

  if ! [ -f "${file}" ]; then
    printf "Internal Error (is_copyright_valid): ${file} is not a valid file.\n"
    exit 1
  fi

  local file_extension="${file##*.}"
  local offset=1

  # since PSR-12 the php files will contain the copyright license as
  # document-level comment, which starts on line 3.
  if [ "php" = "${file_extension}" ]; then
    offset=3
  fi

  for copyright_line in "${COPYRIGHT_LINES[@]}"; do
    local line_to_check="$(sed "${offset}q;d" "${file}")"
    if ! [ "${copyright_line}" = "${line_to_check}" ]; then
      return 1
    fi

    offset=$((1 + ${offset}))
  done

  return 0
}

# DESC: prints all .php and .js files of the provided directory,
#       ignoring the /libs and /node_modules folders.
#
# ARGS: [<string>] directory to scan
function get_supported_files_of_dir() {
  local directory="${1}"

  if ! [ -d "${directory}" ]; then
    printf "Internal Error (get_supported_files_of_dir): ${directory} is not a valid directory.\n"
    exit 1
  fi

  find "${directory}" \( -name "*.php" -or -name "*.js" \) ! -path "*/node_modules/*" ! -path "*/vendor/*"
}

# DESC: returns 0 if the given path is located in the examples
#       directory, 1 otherwise
#
# ARGS: [<string>] file path to check
function is_ui_example() {
  local file="${1}"

  if ! [ -f "${file}" ]; then
    printf "Internal Error (is_ui_example): ${file} is not a valid file.\n"
    exit 1
  fi

  file="$(realpath ${file})"
  if [[ "${file}" == *"components/ILIAS/UI/src/examples"* ]]; then
    return 0
  fi

  return 1
}

# DESC: main function of this script, which executes the copyright-
#       check for either all or the provided file(s).
#       if any of the checked files are invalid they are printed to
#       stdout.
#
# ARGS: [...<string>] file(s) to check, defaults to all or changed
#                     files (from git).
function perform_copyright_check() {
  local files=()
  while [ 1 -le ${#} ]; do
    local file="${1}"

    if [ -d "${file}" ]; then
      files+=($(get_supported_files_of_dir "${file}"))
    elif [ -f "${file}" ]; then
      files+=("${file}")
    else
      printf "Error: ${file} is not a valid file or directory.\n"
      exit 1
    fi

    shift 1>/dev/null
  done

  if [ 0 -eq ${#files[@]} ]; then
    [ -z "${GHRUN}" ] &&
      files=($(get_supported_files_of_dir "$(pwd)")) ||
      files=($(get_changed_files))
  fi

  local exit_status=0
  for file in ${files[@]}; do
    # skip files which don't exist to take care of deleted
    # files when provided by git.
    if ! [ -f "${file}" ]; then
      continue
    fi

    is_copyright_valid "${file}"
    local is_valid="${?}"

    is_ui_example "${file}"
    local is_example="${?}"

    # invert the copyright-check for UI examples, because we
    # don't want them to have too much content.
    if ([ 0 -eq ${is_example} ] && [ 0 -eq ${is_valid} ]) ||
      ([ 1 -eq ${is_example} ] && [ 1 -eq ${is_valid} ]); then
      printf "copyright is not as expected in %s\n" "${file}"
      exit_status=1
    fi
  done

  return ${exit_status}
}

# this helper is only required if we are in a GitHub-run.
if ! [ -z "${GHRUN}" ]; then
  source "$(pwd)/CI/Import/Functions.sh"
fi

# run script with all supplied arguments and exit with the status code
# of the function call.
perform_copyright_check "${@}"
exit "${?}"
