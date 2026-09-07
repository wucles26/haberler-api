---
paths:
  - railway.json
  - package.json
  - .nvmrc
---

# Railway

## Railway CLI logs

Project is linked via Railway CLI (`positive-inspiration` / `haberler-api` / `production`).
After `railway login` and `railway link`, inspect remote failures with:
`railway logs --lines 100`
`railway logs --http --status 500 --lines 20`
`railway logs -b --latest --lines 50`
`railway logs -d --latest --lines 50`
Prefer these over guessing from the generic production 500 page (`APP_DEBUG=false`).

## Railway build must compile Vite assets

`public/build` is gitignored.
`railway.json` `buildCommand` must run Composer plus `npm ci` and `npm run build`.
Without the frontend build, Blade `@vite` pages return 500 with a missing Vite manifest while `/up` can still succeed.

## Railway Node version for Vite 8

Use the `RAILPACK` builder with Node `22.14.0` (`.nvmrc` + `package.json` `engines`).
Nixpacks defaulted to Node 18 and broke Vite 8. Avoid pinning an old `nixpacks.toml` `nixpkgsArchive` that forces full package recompilation.
