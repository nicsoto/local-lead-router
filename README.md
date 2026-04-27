# Local Lead Router

Local Lead Router is a lightweight WordPress plugin for local service businesses that need to capture leads, route them to the right inbox, and manage follow-up inside WordPress.

The plugin code lives in [`local-lead-router`](local-lead-router).

## Local Development

Start WordPress with Docker:

```bash
docker compose up -d
```

Then open `http://localhost:8080`, finish the WordPress install, activate Local Lead Router, and add this shortcode to a page:

```text
[lead_router_form]
```

## Checks

```bash
bash scripts/lint.sh
```

## Build

```bash
bash scripts/build-zip.sh
```

The generated ZIP is written to `dist/`.

## Release

Push a version tag to build and publish a GitHub release artifact:

```bash
git tag v0.4.0
git push origin v0.4.0
```
