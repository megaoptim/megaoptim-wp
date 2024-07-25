#!/bin/bash

SCRIPT_DIR="$(
  cd -- "$(dirname "$0")" >/dev/null 2>&1
  pwd -P
)"
PLUGIN_DIR="$(
  cd -- "$(dirname "$SCRIPT_DIR")" >/dev/null 2>&1
  pwd -P
)"
PLUGINS_ROOT_DIR="$(
  cd -- "$(dirname "$PLUGIN_DIR")" >/dev/null 2>&1
  pwd -P
)"
PLUGIN_SLUG=$(basename $PLUGIN_DIR)

if [ -f "$PLUGINS_ROOT_DIR/$PLUGIN_SLUG.zip" ]; then
  rm "$PLUGINS_ROOT_DIR/$PLUGIN_SLUG.zip"
fi

cd "$PLUGINS_ROOT_DIR"

zip -r "$PLUGIN_SLUG.zip" "$PLUGIN_SLUG" \
  -x="*scripts*" \
  -x="*.git*"

echo "New version ready."
