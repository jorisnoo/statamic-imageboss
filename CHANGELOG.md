# Changelog

All notable changes to `statamic-imageboss` will be documented in this file.

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
