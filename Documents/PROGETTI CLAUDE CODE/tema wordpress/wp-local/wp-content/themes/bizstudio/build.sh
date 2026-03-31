#!/usr/bin/env bash
# ============================================================
#  BizStudio — Simple build pipeline (Sprint 7.9)
#
#  Concatenates and minifies CSS/JS into dist/ bundles.
#  The theme checks for dist files at runtime and uses them
#  when present; otherwise it falls back to src/.
#
#  Usage:
#    chmod +x build.sh
#    ./build.sh          # build both CSS and JS
#    ./build.sh css      # build CSS only
#    ./build.sh js       # build JS only
#    ./build.sh clean    # remove dist files
# ============================================================

set -euo pipefail

THEME_DIR="$(cd "$(dirname "$0")" && pwd)"
SRC_CSS="$THEME_DIR/assets/src/css"
SRC_JS="$THEME_DIR/assets/src/js"
DIST_DIR="$THEME_DIR/assets/dist"

# Colours for terminal output
RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
NC='\033[0m' # No colour

info()  { echo -e "${CYAN}[build]${NC} $1"; }
ok()    { echo -e "${GREEN}[build]${NC} $1"; }
error() { echo -e "${RED}[build]${NC} $1" >&2; }

# ── Helpers ──────────────────────────────────────────────────

# Basic CSS minification: remove comments, collapse whitespace
minify_css() {
    # 1. Remove multi-line comments (/* ... */)
    # 2. Remove lines that are only whitespace
    # 3. Collapse multiple spaces/tabs to single space
    # 4. Remove spaces around { } ; : ,
    # 5. Remove trailing semicolons before }
    # 6. Remove leading/trailing whitespace per line
    sed 's|/\*[^*]*\*\+\([^/*][^*]*\*\+\)*/||g' \
        | tr '\n' ' ' \
        | sed -E '
            s|/\*[^*]*\*+([^/*][^*]*\*+)*/||g
            s|[[:space:]]+| |g
            s| ?\{ ?|{|g
            s| ?\} ?|}|g
            s| ?; ?|;|g
            s| ?: ?|:|g
            s| ?, ?|,|g
            s|;}|}|g
            s|^ ||
            s| $||
        '
}

# Basic JS minification: remove single-line comments (careful with URLs),
# remove multi-line comments, collapse whitespace
minify_js() {
    # 1. Remove multi-line comments
    # 2. Remove single-line comments (// ...) but not URLs (://)
    # 3. Collapse whitespace
    sed -E 's|/\*[^*]*\*+([^/*][^*]*\*+)*/||g' \
        | sed -E 's|([^:])//.*$|\1|g' \
        | tr '\n' ' ' \
        | sed -E '
            s|/\*[^*]*\*+([^/*][^*]*\*+)*/||g
            s|[[:space:]]+| |g
            s|^ ||
            s| $||
        '
}

# ── Clean ────────────────────────────────────────────────────

do_clean() {
    info "Cleaning dist directory..."
    rm -f "$DIST_DIR/style.min.css" "$DIST_DIR/script.min.js"
    ok "Dist files removed."
}

# ── Build CSS ────────────────────────────────────────────────

do_css() {
    info "Building CSS..."

    mkdir -p "$DIST_DIR"

    # Concatenation order matters: main first, then components, then WC
    local css_files=(
        "$SRC_CSS/main.css"
    )

    # Components (alphabetical)
    for f in "$SRC_CSS"/components/*.css; do
        [ -f "$f" ] && css_files+=("$f")
    done

    # WooCommerce
    for f in "$SRC_CSS"/woocommerce/*.css; do
        [ -f "$f" ] && css_files+=("$f")
    done

    # Animations (if separate directory)
    for f in "$SRC_CSS"/animations/*.css; do
        [ -f "$f" ] && css_files+=("$f")
    done

    # Base (if separate directory)
    for f in "$SRC_CSS"/base/*.css; do
        [ -f "$f" ] && css_files+=("$f")
    done

    # Layout (if separate directory)
    for f in "$SRC_CSS"/layout/*.css; do
        [ -f "$f" ] && css_files+=("$f")
    done

    local tmp="$DIST_DIR/.css_concat.tmp"

    # Concatenate with file separators
    : > "$tmp"
    for f in "${css_files[@]}"; do
        echo "/* === $(basename "$f") === */" >> "$tmp"
        cat "$f" >> "$tmp"
        echo "" >> "$tmp"
    done

    # Minify
    minify_css < "$tmp" > "$DIST_DIR/style.min.css"
    rm -f "$tmp"

    local size
    size=$(wc -c < "$DIST_DIR/style.min.css" | tr -d ' ')
    local kb=$(( size / 1024 ))
    ok "style.min.css created (${kb}KB / ${size} bytes)"
}

# ── Build JS ─────────────────────────────────────────────────

do_js() {
    info "Building JS..."

    mkdir -p "$DIST_DIR"

    # Concatenation order: main first, then modules
    local js_files=(
        "$SRC_JS/main.js"
    )

    # Modules (alphabetical)
    for f in "$SRC_JS"/modules/*.js; do
        [ -f "$f" ] && js_files+=("$f")
    done

    # Animations (if separate directory)
    for f in "$SRC_JS"/animations/*.js; do
        [ -f "$f" ] && js_files+=("$f")
    done

    local tmp="$DIST_DIR/.js_concat.tmp"

    # Concatenate with IIFE wrappers to avoid scope leaks
    : > "$tmp"
    for f in "${js_files[@]}"; do
        echo "/* === $(basename "$f") === */" >> "$tmp"
        echo ";(function(){" >> "$tmp"
        cat "$f" >> "$tmp"
        echo "" >> "$tmp"
        echo "})();" >> "$tmp"
    done

    # Minify
    minify_js < "$tmp" > "$DIST_DIR/script.min.js"
    rm -f "$tmp"

    local size
    size=$(wc -c < "$DIST_DIR/script.min.js" | tr -d ' ')
    local kb=$(( size / 1024 ))
    ok "script.min.js created (${kb}KB / ${size} bytes)"
}

# ── Main ─────────────────────────────────────────────────────

case "${1:-all}" in
    css)
        do_css
        ;;
    js)
        do_js
        ;;
    clean)
        do_clean
        ;;
    all)
        do_css
        do_js
        ok "Build complete!"
        ;;
    *)
        error "Unknown command: $1"
        echo "Usage: $0 [css|js|clean|all]"
        exit 1
        ;;
esac
