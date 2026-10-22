#!/usr/bin/env node
"use strict";

/*
 * build-assets.js — dependency-free asset minifier for D&A Honey Store.
 *
 * Regenerates the *.min.css / *.min.js files from their sources so the
 * shipped (minified) files never drift behind the sources again.
 *
 * Usage:
 *   node build-assets.js          # build all
 *   node build-assets.js --check  # verify min files are up to date (exit 1 if not)
 *
 * Targets:
 *   assets/css/style.css      -> assets/css/style.min.css
 *   assets/css/admin.css      -> assets/css/admin.min.css
 *   assets/js/main.js         -> assets/js/main.min.js
 *   assets/js/admin.js        -> assets/js/admin.min.js
 */

const fs = require("fs");
const path = require("path");

const ROOT = __dirname;

const TARGETS = [
  { src: "assets/css/style.css", out: "assets/css/style.min.css", kind: "css" },
  { src: "assets/css/admin.css", out: "assets/css/admin.min.css", kind: "css" },
  { src: "assets/js/main.js", out: "assets/js/main.min.js", kind: "js" },
  { src: "assets/js/admin.js", out: "assets/js/admin.min.js", kind: "js" },
];

// --- CSS minification ------------------------------------------------------
// Strips comments, collapses whitespace, and removes redundant spaces around
// structural characters. String literals and url() contents are preserved.
function minifyCss(input) {
  let out = "";
  let i = 0;
  const n = input.length;
  while (i < n) {
    const ch = input[i];
    // Comment
    if (ch === "/" && input[i + 1] === "*") {
      const end = input.indexOf("*/", i + 2);
      i = end === -1 ? n : end + 2;
      continue;
    }
    // String literal (single or double quoted) — copy verbatim
    if (ch === '"' || ch === "'") {
      out += ch;
      i++;
      while (i < n) {
        out += input[i];
        if (input[i] === "\\") {
          i++;
          if (i < n) out += input[i];
          i++;
          continue;
        }
        if (input[i] === ch) {
          i++;
          break;
        }
        i++;
      }
      continue;
    }
    out += ch;
    i++;
  }
  // Collapse whitespace
  out = out.replace(/\s+/g, " ");
  // Remove spaces around structural punctuation
  out = out.replace(/\s*([{}:;,>~+])\s*/g, "$1");
  out = out.replace(/;}/g, "}");
  out = out.replace(/ ?! ?/g, "!");
  // Preserve a space in selectors between combinators is unnecessary; but
  // descendant combinator must remain a single space — the replace above
  // keeps a single space where needed because \s+ already collapsed it.
  return out.trim();
}

// --- JS minification -------------------------------------------------------
// Conservative: strips /* */ and // comments plus leading indentation, but does
// NOT rename or fold code, so runtime behavior is identical to the source.
// A full "true minifier" is intentionally avoided since no external tool is
// available offline; correctness is preferred over byte count.
function minifyJs(input) {
  let out = "";
  let i = 0;
  const n = input.length;
  let prev = "";
  while (i < n) {
    const ch = input[i];
    const next = input[i + 1];
    // Block comment
    if (ch === "/" && next === "*") {
      const end = input.indexOf("*/", i + 2);
      i = end === -1 ? n : end + 2;
      continue;
    }
    // Line comment (only when not part of a URL like http://)
    if (ch === "/" && next === "/" && !/[:\w]/.test(prev) === false && prev !== ":") {
      // Heuristic: treat as comment unless preceded by ':' (protocol)
      if (prev !== ":") {
        let end = input.indexOf("\n", i + 2);
        i = end === -1 ? n : end;
        continue;
      }
    }
    // String literal
    if (ch === '"' || ch === "'" || ch === "`") {
      out += ch;
      i++;
      while (i < n) {
        out += input[i];
        if (input[i] === "\\") {
          i++;
          if (i < n) out += input[i];
          i++;
          continue;
        }
        if (input[i] === ch) {
          i++;
          break;
        }
        i++;
      }
      prev = ch;
      continue;
    }
    out += ch;
    prev = ch;
    i++;
  }
  // Collapse indentation/newlines but keep newlines semantic (ASI safety):
  // replace runs of spaces/tabs with one space, drop spaces before newlines.
  out = out.replace(/[ \t]+/g, " ");
  out = out.replace(/ *\n */g, "\n");
  out = out.replace(/\n{2,}/g, "\n");
  return out.trim();
}

// --- Identifier preservation check ----------------------------------------
// Guarantees minification did not drop any identifier present in the source,
// which is what silently broke the previous main.min.js.
function stripComments(src, kind) {
  // Remove block and line comments so comment words are not mistaken for code
  // identifiers during the preservation check.
  let out = "";
  let i = 0;
  const n = src.length;
  let prev = "";
  while (i < n) {
    const ch = src[i];
    const next = src[i + 1];
    if (ch === "/" && next === "*") {
      const end = src.indexOf("*/", i + 2);
      i = end === -1 ? n : end + 2;
      continue;
    }
    if (kind === "js" && ch === "/" && next === "/" && prev !== ":") {
      let end = src.indexOf("\n", i + 2);
      i = end === -1 ? n : end;
      continue;
    }
    if (ch === '"' || ch === "'" || ch === "`") {
      out += ch;
      i++;
      while (i < n) {
        out += src[i];
        if (src[i] === "\\") {
          i++;
          if (i < n) out += src[i];
          i++;
          continue;
        }
        if (src[i] === ch) {
          i++;
          break;
        }
        i++;
      }
      prev = ch;
      continue;
    }
    out += ch;
    prev = ch;
    i++;
  }
  return out;
}

function assertIdentifiersPreserved(src, out, kind) {
  const identRe = kind === "js" ? /[A-Za-z_$][\w$]{2,}/g : /[.#][A-Za-z_-][\w-]{2,}/g;
  const srcCode = stripComments(src, kind);
  const srcIds = new Set((srcCode.match(identRe) || []));
  const outIds = new Set((out.match(identRe) || []));
  const missing = [];
  for (const id of srcIds) {
    if (!outIds.has(id)) missing.push(id);
  }
  return missing;
}

function build(check) {
  let failed = false;
  for (const t of TARGETS) {
    const srcPath = path.join(ROOT, t.src);
    const outPath = path.join(ROOT, t.out);
    if (!fs.existsSync(srcPath)) {
      console.error(`  ✗ missing source: ${t.src}`);
      failed = true;
      continue;
    }
    const src = fs.readFileSync(srcPath, "utf8");
    const min = t.kind === "css" ? minifyCss(src) : minifyJs(src);
    const missing = assertIdentifiersPreserved(src, min, t.kind);
    if (missing.length) {
      console.error(`  ✗ ${t.out}: minifier dropped ${missing.length} identifiers: ${missing.slice(0, 8).join(", ")}`);
      failed = true;
      continue;
    }
    const finalContent = min + "\n";
    if (check) {
      const existing = fs.existsSync(outPath) ? fs.readFileSync(outPath, "utf8") : "";
      if (existing.trim() !== min.trim()) {
        console.error(`  ✗ ${t.out} is OUT OF DATE (run: node build-assets.js)`);
        failed = true;
      } else {
        console.log(`  ✓ ${t.out} is up to date`);
      }
      continue;
    }
    fs.writeFileSync(outPath, finalContent);
    const pct = Math.round((1 - min.length / src.length) * 100);
    console.log(`  ✓ ${t.out}  (${src.length} → ${min.length} bytes, -${pct}%)`);
  }
  return failed;
}

const check = process.argv.includes("--check");
console.log(check ? "Checking minified assets..." : "Building minified assets...");
const failed = build(check);
if (failed) {
  process.exit(1);
}
console.log(check ? "All minified assets are up to date." : "Done.");
