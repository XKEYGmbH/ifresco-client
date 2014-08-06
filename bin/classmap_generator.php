#!/usr/bin/env sh
SRC_DIR="`pwd`"
cd "`dirname "$0"`"
cd "../vendor/zendframework/zendframework/bin"
BIN_TARGET="`pwd`/classmap_generator.php"
cd "$SRC_DIR"
"$BIN_TARGET" "$@"
