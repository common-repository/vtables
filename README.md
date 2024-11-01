# vTables (vtables)

Create stunning, interactive tables effortlessly. No coding required. The ultimate WordPress plugin, lets you design powerful tables in a few clicks. Transform your data visualization now.

## Install the dependencies

```bash
yarn
# or
npm install
```

### Start the app in development mode (hot-code reloading, error reporting, etc.)

```bash
quasar dev
```

### Lint the files

```bash
yarn lint
# or
npm run lint
```

### Format the files

```bash
yarn format
# or
npm run format
```

### Build the app for production

```bash
quasar build
```

### Connect Back-End

Create `env.php` in the root folder with this content:

```
<?php
define( 'VTABLES_MODE', 'dev' );
define( 'VTABLES_BASE_URL', 'http://localhost:8000' );
```

Set VTABLES_MODE to 'dev' for development, and 'prod' when build production version.
Set VTABLES_BASE_URL to URL that used for development.

### Customize the configuration

See [Configuring quasar.config.js](https://v2.quasar.dev/quasar-cli-vite/quasar-config-js).
