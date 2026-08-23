# WoowGallery — Agent Instructions

<!-- Capsule contract: this file is the project capsule (ADR-002).
     Sections marked (required) must exist; `agentic doctor` checks them. -->

## Project (required)
- id: woowgallery
- type: wordpress-plugin
- owner: simka
- memory: agentic-vault/Projects/woowgallery/ — durable notes, decisions,
  receipts live THERE; this repo holds only working files.

## Session continuity (required)
At session start, READ the vault working state and continue from its
"In progress"/"Next". State path (ADR-003, resolver `lib/task-path.sh`):
`Projects/woowgallery/current.md` on main/master; on any other branch
`Projects/woowgallery/tasks/<branch-slug>/current.md`. At checkpoints and
session end, UPDATE it and append a journal entry (handoff skill). This is how
work survives switching between Claude Code / Codex / Antigravity / Devin —
and between parallel worktrees — mid-task.

## Verification (required)
Run before claiming any change done:
```bash
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
```

## Connectors (required)
Accounts this project may touch. Connector skills call the service CLI/API via
`agentic secrets run <profile> -- ...` (ADR-002 §3); anything not listed = out of scope.
| Service | Account/workspace | Access |
|---|---|---|
| github | pasyuk (pasyuk/woowgallery) | read-write via PR |

Freemius and WordPress.org (plugin distribution) and the Instagram API
(runtime plugin feature) are touched by the plugin itself, not by agents —
no agent-side connector; releases are human-driven (see Protected actions).

## Protected actions (required)
These ALWAYS require explicit human approval (enforced by the PreToolUse hook,
documented here for humans + non-hook harnesses):
send message · create/update external issue · merge PR · bulk delete
· rotate/modify secrets · anything billing
· deploy/release to WordPress.org or Freemius · version bump in
`woowgallery.php`/`readme.txt` stable tag.

## Working rules
- Evidence before verdict: a "done/passing" claim requires a citation
  (file:line, test name, command output) written BEFORE the claim. No
  citation → state UNPROVEN.
- 2-strike rule: same issue survives 2 fix attempts → STOP patching, write
  diagnostic, ask the user.
- Read-before-write; glob before creating a file (no silent shadowing);
  destructive steps need a stated rollback plan covering untracked files.

## Knowledge graph
After modifying code, run `graphify update .`. For codebase questions, query
`graphify query "<question>"` before grepping.

## Skills
Universal skills come from the agentic-os kernel (linked). Project skills live
in `.agents/skills/` (canonical; harness dirs are symlinks via `agentic link`).
Rule: a skill that names an account, channel, table, or command is a PROJECT
skill; process-only skills go to the kernel.

Optional overrides: `./AGENTS.override.md` then `~/.config/agentic/override.md`
— later wins; may narrow, must not relax protected actions.

# Repository Guidelines

## Project Structure & Module Organization

This repository is the `woowgallery` WordPress plugin. The entrypoint is `woowgallery.php`, with the main bootstrap class in `class-woowgallery.php`. Core PHP classes live in `includes/`, shared helpers in `functions/`, admin templates in `includes/admin/templates/`, and skins in `skins/{amron,parallax,multigrid}/`. Assets live in `assets/`: edit sources in `assets/js/src/` and `assets/css/*.scss`, then keep generated `.js`, `.css`, and `.map` files in sync. Bundled third-party code lives in `vendor/` and `assets/vendor/`; avoid editing it directly.

## Build, Test, and Development Commands

There is no committed `package.json`, `composer.json`, Makefile, or PHPUnit config. Use the local WordPress CLI wrapper in the Local site:

- `wp-dev plugin status woowgallery` checks activation and the loaded plugin version.
- `wp-dev plugin activate woowgallery` activates the plugin in the local WordPress install.
- `find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l` runs PHP syntax checks on plugin-owned PHP files.

Asset compilation appears to be managed by CodeKit via `assets/.config.codekit3`; do not hand-edit that config.

## Coding Style & Naming Conventions

Follow the existing WordPress-style PHP formatting: tabs for indentation, spaced function calls such as `defined( 'ABSPATH' )`, snake_case helpers, and namespaced classes under `WoowGallery`. Class files use the `class-name.php` pattern expected by `includes/class-autoload.php`. Keep hooks, nonces, sanitization, and escaping close to nearby WordPress API usage. JavaScript in `assets/js/src/` uses two-space indentation, jQuery/Vue patterns, and existing `woowgallery` globals.

## Testing Guidelines

No automated test suite is currently present. At minimum, run PHP lint before handoff. For behavior changes, verify manually in the relevant WordPress admin and frontend flows: gallery editing, dynamic gallery queries, skins, Gutenberg/Elementor integration, WooCommerce templates, or lightbox behavior. Note the exact manual checks in the PR.

## Commit & Pull Request Guidelines

Recent history favors concise release or maintenance summaries, for example `Version bump to 1.2.4` or `Added backward compatibility functions for WordPress and PHP versions`. Keep commits scoped and mention version/readme changes explicitly. PRs should include a short description, affected areas, manual test notes, linked issues, and screenshots for visible admin/frontend changes.

## Security & Configuration Tips

Do not commit local environment files, credentials, or Freemius/private service keys. Preserve the `woowgallery` text domain and plugin version metadata in `woowgallery.php` and `readme.txt` when preparing releases.
