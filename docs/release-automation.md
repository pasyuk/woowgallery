# WoowGallery release automation

This workflow builds a source archive, creates a **pending** Freemius
deployment, downloads and verifies the exact free archive, and prepares a local
WordPress.org SVN checkout for inspection. Normal sequence stops before publication:
it neither commits SVN nor changes Freemius to `released`.

WoowGallery is closed on WordPress.org pending review. An approved SVN commit
does not prove that WordPress.org has reopened or distributed the plugin; check
and report that external state separately.

## Prerequisites

Install Bash, Git, PHP, Node.js, `jq`, `curl`, `zip`, `unzip`, `rsync`,
`shasum`, a WordPress CLI capable of `dist-archive`, and Subversion for the SVN
stages. Keep an existing checkout of
`https://plugins.svn.wordpress.org/woowgallery/` outside the plugin repository.
Never install a generated ZIP over the Git checkout.

Configure two executable artifact runners. `WOOWGALLERY_ARTIFACT_PLUGIN_CHECK_CMD`
must run Plugin Check against the extracted path passed as its only argument.
`WOOWGALLERY_ARTIFACT_ACTIVATION_CMD` must install that same path into a
disposable WordPress environment, activate `woowgallery`, and exit non-zero on
installation, activation, or bootstrap errors. Neither runner may install over
the Git checkout.

Provide `FREEMIUS_API_TOKEN` through the environment or an operator-owned
secret runner. The runner may inject an already-exported variable, or use the
silent prompt below. Do not put the token in this repository, a `.env` file, the release
manifest, a shell history entry, or a command argument. Use the local SVN
credential store; the CLI intentionally accepts no username or password.

## Safe sequence

Run from a clean `master` that exactly matches `origin/master`. Replace every
angle-bracket placeholder locally; the examples contain no credentials.

```bash
IFS= read -r -s -p 'Freemius API token: ' FREEMIUS_API_TOKEN
printf '\n'
export FREEMIUS_API_TOKEN

bin/woowgallery-release preflight --repo . --version <version>
bin/woowgallery-release build --repo . --version <version> --work-dir <new-work-dir>
bin/woowgallery-release freemius-upload --manifest <manifest-path>
bin/woowgallery-release freemius-download --manifest <manifest-path>
WOOWGALLERY_ARTIFACT_PLUGIN_CHECK_CMD='<plugin-check-runner>' \
WOOWGALLERY_ARTIFACT_ACTIVATION_CMD='<isolated-install-activation-runner>' \
  bin/woowgallery-release verify --manifest <manifest-path>
bin/woowgallery-release svn-prepare --manifest <manifest-path> --checkout <svn-checkout-path>
```

At this point Freemius remains `pending` and SVN remains uncommitted. There is
no catch-all release command.

Inspect the non-secret evidence before requesting either protected action:

```bash
jq . <manifest-path>
cat <work-dir>/freemius-transformations.txt
svn status <svn-checkout-path>
svn diff --summarize <svn-checkout-path>
svn diff <svn-checkout-path>
diff -qr --exclude=.svn <verified-free-root> <svn-checkout-path>/trunk
diff -qr --exclude=.svn <verified-free-root> <svn-checkout-path>/tags/<version>
```

## Protected publication

`svn-publish` and `freemius-release` are independent actions. Each needs a
separate explicit approval immediately before execution and the byte-exact
confirmation printed by the manifest's version and full 40-character Git SHA.
Approval for one does not approve the other.

```bash
bin/woowgallery-release svn-publish \
  --manifest <manifest-path> \
  --confirm 'publish svn <version> <full-git-sha>'

bin/woowgallery-release freemius-release \
  --manifest <manifest-path> \
  --confirm 'release freemius <version> <full-git-sha>'
```

The SVN command revalidates the prepared checkout, artifact, status, diff, and
remote tag immediately before its single commit. The Freemius command
re-fetches the exact recorded pending deployment before its single release
request. Neither command accepts authentication secrets as arguments.

## Failure and recovery

- Before publication, keep the work directory for inspection. A failed
  Freemius upload remains non-public; discard and recreate a local SVN checkout
  if preparation fails.
- If the downloaded ZIP, manifest hashes, verification evidence, checkout
  identity, status, diff, or remote tag changes, stop and repeat the safe stage
  that owns that evidence. Do not edit the manifest by hand.
- After a potentially successful SVN commit or Freemius release request, do
  not retry automatically. Inspect the remote deployment or SVN revision first;
  the external mutation may have succeeded before local evidence was written.
- An incorrect published artifact requires a new corrective version. Never
  overwrite an existing WordPress.org tag.

Version bumps, commits, pushes, Freemius upload/release, and SVN publication
remain separate operator decisions. The commands above are examples only, not
authorization to perform them.
