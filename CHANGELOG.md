# Changelog

All notable changes to `statamic-imageboss` will be documented in this file.

## [0.4.0](https://github.com/jorisnoo/statamic-imageboss/releases/tag/v0.4.0) (2026-07-10)

### Features

- add animation support and default focal points to center ([cec6bb7](https://github.com/jorisnoo/statamic-imageboss/commit/cec6bb74d54c22123fef96c06f1d50a3dcf76a5d))
- add automatic imageboss cache purging on asset reupload ([bc6ed95](https://github.com/jorisnoo/statamic-imageboss/commit/bc6ed95df8f27fd9e866bd70dc2402e44a1052c8))

### Code Refactoring

- rename secret to token for ImageBoss URL signing ([09955ac](https://github.com/jorisnoo/statamic-imageboss/commit/09955ac9e3d927308242f43b626e6d8bac5a7eb9))

### Documentation

- update requirements to php 8.3+ and statamic 5-6 ([01e89da](https://github.com/jorisnoo/statamic-imageboss/commit/01e89da4ab1685301d235c3b798b1221657147e9))

### Tests

- refactor ImageBossBuilder tests with extracted mock helpers ([24dc514](https://github.com/jorisnoo/statamic-imageboss/commit/24dc51492a763f7a18e4b9c2cbdc084bdccdb715))
- expand ImageBossBuilder test suite and upgrade Pest to v4 ([c2fcfca](https://github.com/jorisnoo/statamic-imageboss/commit/c2fcfca8807284e122a37c266f2a795ef632baad))

### Build System

- **deps:** bump actions/checkout from 6 to 7 ([95546f6](https://github.com/jorisnoo/statamic-imageboss/commit/95546f640f12be196ef2d99e9c351fa34439e58e))

### Chores

- drop Laravel 11 support, require Laravel 12 minimum ([9fce458](https://github.com/jorisnoo/statamic-imageboss/commit/9fce458a144771446448a0332a738c5151ce35cf))
- simplify dependabot auto-merge workflow ([0f3c2f8](https://github.com/jorisnoo/statamic-imageboss/commit/0f3c2f883aae56a39d2d7ba48879ddde65b7d606))
- drop php 8.2 support ([6627c87](https://github.com/jorisnoo/statamic-imageboss/commit/6627c87f4d18a5bfc80738e3ef88f3c84457918e))
## [0.3.2](https://github.com/jorisnoo/statamic-imageboss/releases/tag/v0.3.2) (2026-03-31)

### Chores

- remove workflows and update dependencies ([0e85ee2](https://github.com/jorisnoo/statamic-imageboss/commit/0e85ee2c17123e2674a6f9e5343810fe4282b0c0))
## [0.3.1](https://github.com/jorisnoo/statamic-imageboss/releases/tag/v0.3.1) (2026-02-24)

### Features

- add placeholder method to generate inline SVG data URIs for layout stability ([9719cf9](https://github.com/jorisnoo/statamic-imageboss/commit/9719cf94c884caee3dbcf2a1b04cdd06b8738867))
- replace null return with NullImageBossBuilder null object pattern for safer chaining ([d85eabe](https://github.com/jorisnoo/statamic-imageboss/commit/d85eabe5678c266a9a11e394266090712c950085))
- return null from ImageBoss::from() when asset is null instead of throwing ([5e57764](https://github.com/jorisnoo/statamic-imageboss/commit/5e577648f955c3d15bea94ae903648a3cbb39533))

### Bug Fixes

- support three-part focal point format in focus string validation ([69513fc](https://github.com/jorisnoo/statamic-imageboss/commit/69513fc1192c56910fd02efd79e2034b4251501f))
- add crop_focal fit when height is specified in image manipulation ([2e75b89](https://github.com/jorisnoo/statamic-imageboss/commit/2e75b89b3740235843da24f873ec39925cca34bd))
- update publish tag and config path references in README and service provider ([b1ac9f8](https://github.com/jorisnoo/statamic-imageboss/commit/b1ac9f89cde125be592cc8e8dee9a810b8a5630b))
## [0.3.0](https://github.com/jorisnoo/statamic-imageboss/releases/tag/v0.3.0) (2026-02-13)

### Code Refactoring

- move config to statamic namespace (statamic.imageboss) ([48c783f](https://github.com/jorisnoo/statamic-imageboss/commit/48c783fd0fe8cf71b00935bafafa832d133aa1a9))
## [0.2.0](https://github.com/jorisnoo/statamic-imageboss/releases/tag/v0.2.0) (2026-02-11)

### Bug Fixes

- add input validation and path sanitization ([1048d40](https://github.com/jorisnoo/statamic-imageboss/commit/1048d40ce302787ce00f9896d1111badd63044e0))

### Code Refactoring

- simplify ([95f419a](https://github.com/jorisnoo/statamic-imageboss/commit/95f419a2551fd649d3ff96028b3a98fc8eff1e71))
- simplify tag methods using nullable setters ([de5cf79](https://github.com/jorisnoo/statamic-imageboss/commit/de5cf796b1f1b115628433ca301f1c158ed3455c))

### Build System

- allow statamic 6 ([c96319a](https://github.com/jorisnoo/statamic-imageboss/commit/c96319aa9f2e92f8f6f442352c6c12941e7637a1))
- add support URLs and author homepage for Packagist ([a445bfa](https://github.com/jorisnoo/statamic-imageboss/commit/a445bfa5629c42c84884443aa3f54f2b0f419c90))
## [0.1.1](https://github.com/jorisnoo/statamic-imageboss/releases/tag/v0.1.1) (2026-01-21)

### Features

- add nullable setters and aspectRatio() method ([6d594db](https://github.com/jorisnoo/statamic-imageboss/commit/6d594dbd8a023326cfa03acec9126839e71a58a3))

### Build System

- link to changelog in releases ([9c15e23](https://github.com/jorisnoo/statamic-imageboss/commit/9c15e23512ec746b2bff966e3eb6e7b81ddb55d3))
## [0.1.0](https://github.com/jorisnoo/statamic-imageboss/releases/tag/v0.1.0) (2026-01-21)

### Features

- add RIAS helper ([5050e03](https://github.com/jorisnoo/statamic-imageboss/commit/5050e035ca3f8a7af61589d8eeb7202dae12ccdb))
- add interface-based preset support ([f2b0e99](https://github.com/jorisnoo/statamic-imageboss/commit/f2b0e998b9f15c67f91cb388b2c9510ab8192588))
- support Value-wrapped assets in ImageBoss::from() ([5c113c2](https://github.com/jorisnoo/statamic-imageboss/commit/5c113c247e9a61ab1618f6bc7630b1f68717fda3))
- add justfile ([653217c](https://github.com/jorisnoo/statamic-imageboss/commit/653217cda8cc4f21671a8b349e2792e1199783ee))
- preset enum ([6aa7816](https://github.com/jorisnoo/statamic-imageboss/commit/6aa7816133734b6bf2d2756156a8cd1d4fca8d9e))
- build package ([8fcc00b](https://github.com/jorisnoo/statamic-imageboss/commit/8fcc00bec85e168ee036e5121e153fd39507c402))
- config package ([f0fa6ff](https://github.com/jorisnoo/statamic-imageboss/commit/f0fa6ffe901f71b017378f955324a3608df5af0e))

### Bug Fixes

- remove signing from RIAS URLs ([f474184](https://github.com/jorisnoo/statamic-imageboss/commit/f4741841690d1fa52b0cd83973a4210b62b664d1))
- testbench ([5220a86](https://github.com/jorisnoo/statamic-imageboss/commit/5220a86fd168d123098db8915a0686a5de5cae07))
- backed enum support ([f55a05c](https://github.com/jorisnoo/statamic-imageboss/commit/f55a05cbd6ef05a3e5936c7c765b1771cb21a37e))
- tests ([a0a3473](https://github.com/jorisnoo/statamic-imageboss/commit/a0a34733210f7bc6f9030f5ef388d3f73d9d0020))
- tests ([e7f76ff](https://github.com/jorisnoo/statamic-imageboss/commit/e7f76ff6b11e936b966506c0d89c3d89f2592430))

### Documentation

- update changelog, docal point example in readme ([b192597](https://github.com/jorisnoo/statamic-imageboss/commit/b192597c9aa4422b92a2705d54a9e26a0cbc3197))
- add example output to readme ([7b22a6a](https://github.com/jorisnoo/statamic-imageboss/commit/7b22a6abd389ce1a74f2fa4eb0a4b6d08e48ede4))
- update readme ([b855261](https://github.com/jorisnoo/statamic-imageboss/commit/b85526111ce58fda31dfe6aeee8138cd0b9f1bee))

### Build System

- release workflow ([d62d2a5](https://github.com/jorisnoo/statamic-imageboss/commit/d62d2a56494e75717c98b313c44c37a82bd61b2d))
## [1.0.0] - 2025-01-14

### Added

- ImageBoss CDN integration with automatic Glide fallback
- Fluent builder API for generating optimized URLs and responsive srcsets
- Focal point and URL signing support
- Antlers tags: `{{ imageboss:url }}` and `{{ imageboss:srcset }}`
- Configurable preset system
