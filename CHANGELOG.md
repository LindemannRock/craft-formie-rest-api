# Changelog

## [3.8.0](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.7.0...v3.8.0) (2026-06-09)


### Added

* add support for sparse fieldsets in submissions API ([98a70de](https://github.com/LindemannRock/craft-formie-rest-api/commit/98a70de87a0fd63a5f591aefdb7b4bae3ea40de5))
* add support for sparse fieldsets in transformSubmissionFields method ([061b2dc](https://github.com/LindemannRock/craft-formie-rest-api/commit/061b2dc9b02c8747890f2657c0c23db05c2efaed))
* **security:** add support for query parameter order normalization in request signature validation ([d30cfc1](https://github.com/LindemannRock/craft-formie-rest-api/commit/d30cfc156b88d218a8d562170c7426066d34ca91))
* sort query parameters alphabetically before signing requests ([00ffbcf](https://github.com/LindemannRock/craft-formie-rest-api/commit/00ffbcf834c8f060928503c4a011ae2793e74d34))

## [3.7.0](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.6.0...v3.7.0) - 2026-06-07


### Added

* add hasReadOnlyCpSettings property to control panel settings ([6b97b6a](https://github.com/LindemannRock/craft-formie-rest-api/commit/6b97b6ace7d3129bb82b9fbe0cf16b0da052b084))
* add Postman collection download to settings test page ([6b5abbd](https://github.com/LindemannRock/craft-formie-rest-api/commit/6b5abbd36a228f6280628a89c15ee0ca51042ae1))

## [3.6.0](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.5.0...v3.6.0) - 2026-06-07


### Added

* add static analysis script for CI workflow ([8c70369](https://github.com/LindemannRock/craft-formie-rest-api/commit/8c703695fa1404d8a27f6bef577c568890381dcb))
* **cli:** add HelpController for cli command assistance ([53e32e2](https://github.com/LindemannRock/craft-formie-rest-api/commit/53e32e2a476bb36518e0dfc1534360f8cc4fccb4))
* **config:** add date/time formatting overrides for plugin settings ([e538d73](https://github.com/LindemannRock/craft-formie-rest-api/commit/e538d732663ccc465d9876fa96f4b88baba30401))
* **i18n:** add interface settings translations for multiple locales ([ddce4f2](https://github.com/LindemannRock/craft-formie-rest-api/commit/ddce4f2d6da847218953d0cbce70cb48b3a58731))
* **i18n:** add missing translation for Plugins ([d886953](https://github.com/LindemannRock/craft-formie-rest-api/commit/d886953e4eb7fa09bba3b0f440f14377ef564876))
* **i18n:** add new API key configuration messages in multiple languages ([3c040b8](https://github.com/LindemannRock/craft-formie-rest-api/commit/3c040b8d9c4313e5ac076c5843af00ca822bec1e))
* **settings:** add interface settings page and navigation link ([e146381](https://github.com/LindemannRock/craft-formie-rest-api/commit/e146381c22d24e0d3d1b399c927cd8cc036fd1d3))
* **settings:** add interface settings page to controller ([e7d7c1c](https://github.com/LindemannRock/craft-formie-rest-api/commit/e7d7c1c8c8d2eee91f2cdb319e0b53cc0d1fe579))
* **settings:** add interface settings route to URL rules ([a1c2a7b](https://github.com/LindemannRock/craft-formie-rest-api/commit/a1c2a7bb64ba0f8b9cb889165e1ba9451b64673e))


### Fixed

* **api:** ensure submissions are complete and not marked as spam ([a9b0db3](https://github.com/LindemannRock/craft-formie-rest-api/commit/a9b0db310f0004a1c4908215510ee0d780e05469))
* **controllers:** clarify API consumer description and standardize API key header ([5bc58bd](https://github.com/LindemannRock/craft-formie-rest-api/commit/5bc58bd46266ac8bc2972846aa8393211f03031e))
* **i18n:** correct Spanish and Dutch settings saved translations ([798b6d7](https://github.com/LindemannRock/craft-formie-rest-api/commit/798b6d78f83f057cb80f47a215deaab264978085))
* **settings:** return localized error message for unconfigured API key ([552b0bb](https://github.com/LindemannRock/craft-formie-rest-api/commit/552b0bb94b4f7039ecd48357fa64380e74c62248))
* **settings:** update API key configuration message for clarity ([eee0c8d](https://github.com/LindemannRock/craft-formie-rest-api/commit/eee0c8d8ef56609767bf42db69f3c2850ce5036e))

## [3.5.0](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.4.0...v3.5.0) - 2026-05-22


### Added

* add pre-commit hook for ECS and PHPStan code quality checks ([ce387f6](https://github.com/LindemannRock/craft-formie-rest-api/commit/ce387f6ed72def1b4d76d463e69bfae5e33ac0eb))
* **i18n:** add translation issue template for reporting language problems ([08bca20](https://github.com/LindemannRock/craft-formie-rest-api/commit/08bca2036ffede85151a62d59f4daa12919f5d9a))
* **tests:** add integration tests for API key and security services ([528ca50](https://github.com/LindemannRock/craft-formie-rest-api/commit/528ca50712f79dccefe228554619ead4bf1cbd8c))


### Fixed

* correct phpstan include path in configuration ([eab62cb](https://github.com/LindemannRock/craft-formie-rest-api/commit/eab62cb10ebe11d2799a5dd93200952290eca07d))

## [3.4.0](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.3.0...v3.4.0) - 2026-05-06


### Features

* **api:** add date filter validation for query parameters ([753ccfe](https://github.com/LindemannRock/craft-formie-rest-api/commit/753ccfec4a87aa3c0c21c46ee4316f7af1b1de45))
* **api:** add error message and visibility to form fields metadata ([fb15bf8](https://github.com/LindemannRock/craft-formie-rest-api/commit/fb15bf83ae66117ee142aeb3f90099e55e597be8))
* **api:** add form-level metadata retrieval for detail endpoint ([cbbeb3e](https://github.com/LindemannRock/craft-formie-rest-api/commit/cbbeb3ee5d201779de9616b89fea61ecb85f42a5))
* **api:** enforce API key permissions for form and submission actions ([65a5b35](https://github.com/LindemannRock/craft-formie-rest-api/commit/65a5b3555bddb7d5a63a575105eb886c59b109bf))
* **api:** enforce HMAC signing for API key validation ([d6e7397](https://github.com/LindemannRock/craft-formie-rest-api/commit/d6e7397e85517b2c1220cf2358062014524052a8))
* **api:** enhance form field processing with new value handling methods ([1e0bfc2](https://github.com/LindemannRock/craft-formie-rest-api/commit/1e0bfc2b6b0a6a7edb2e84e3fa4c17640dc786a3))
* **api:** enhance form fields metadata with appearance and advanced settings ([2ed369e](https://github.com/LindemannRock/craft-formie-rest-api/commit/2ed369ebe5767197459bef3c2baf899316fe9d17))
* **api:** enhance form pages with per-page settings and conditions ([792db43](https://github.com/LindemannRock/craft-formie-rest-api/commit/792db43ad1da70d925d3719d22b3b632f5a31884))
* **api:** exclude incomplete and spam submissions from counts ([6328001](https://github.com/LindemannRock/craft-formie-rest-api/commit/6328001aaba2db6da336cbf3589bb48271ea5da1))
* **api:** implement rate limiting and logging for API access ([7162c3d](https://github.com/LindemannRock/craft-formie-rest-api/commit/7162c3d03af6583289ae118894b5b60b4c895bf1))
* **api:** optimize form and submission count queries ([92dd4df](https://github.com/LindemannRock/craft-formie-rest-api/commit/92dd4dfcecbae87c085b2f7ac6457f4677cffa3c))
* **api:** optimize form submission count retrieval ([9fd09e2](https://github.com/LindemannRock/craft-formie-rest-api/commit/9fd09e23522d062e74c9ddf36b09bd4de0bd7f70))
* **api:** optimize submission count retrieval with batch-fetching ([2b93ed7](https://github.com/LindemannRock/craft-formie-rest-api/commit/2b93ed7d8593cb0a046dba07d8852bafb10f45dd))
* **api:** register test endpoints conditionally based on devMode ([c0bbec0](https://github.com/LindemannRock/craft-formie-rest-api/commit/c0bbec055aa779900293decb251e4c963cd0abea))
* **api:** update API key generation method to use centralized service ([6c677a2](https://github.com/LindemannRock/craft-formie-rest-api/commit/6c677a2bfb1cde058bcf5c5321287c49f6b820cf))
* **cli:** add SecurityController for API key generation ([3e1b15f](https://github.com/LindemannRock/craft-formie-rest-api/commit/3e1b15f7c2e62fee59182f75814da1a7a9a7b121))
* **helper:** add installation experience configuration for API plugin ([f73d19b](https://github.com/LindemannRock/craft-formie-rest-api/commit/f73d19b30fb8186d6c59375f32fcc44afbcbbf0e))
* **issue-templates:** add bug report, feature request, and question templates ([765448f](https://github.com/LindemannRock/craft-formie-rest-api/commit/765448f94216086d0b1ff03e856fd04455c35853))
* **security:** implement HMAC signing for API key validation ([346426c](https://github.com/LindemannRock/craft-formie-rest-api/commit/346426c0d381df30494d51f2af936edfdde8ab68))
* **security:** implement IP whitelist enforcement for API keys ([f3211da](https://github.com/LindemannRock/craft-formie-rest-api/commit/f3211da44bcb6edfdd347e6612899bfafab1e03a))
* **SecurityService:** implement mutex for rate-limit checks ([5278c1a](https://github.com/LindemannRock/craft-formie-rest-api/commit/5278c1a80adb22416d851dd503330c81feea929b))
* **settings:** add missing keys warning and validation summary to settings layout ([0cae9c6](https://github.com/LindemannRock/craft-formie-rest-api/commit/0cae9c61868c85ddf9d4dd6042855c4943cb8dc5))
* **settings:** add settings controller and templates for general and test settings ([ee3dd8f](https://github.com/LindemannRock/craft-formie-rest-api/commit/ee3dd8f46880b81cf73e6b23d0abf1fbc4f7ffdb))
* **transformer:** add FormieTransformerService for field processing and submission transformation ([9c5b2a4](https://github.com/LindemannRock/craft-formie-rest-api/commit/9c5b2a4fa090344d6f7ef812b62e4bb45d80bba4))
* **translations:** add configuration warning messages in multiple languages ([7448687](https://github.com/LindemannRock/craft-formie-rest-api/commit/74486870720577f1cc3442ef442d79c96ff94816))
* **translations:** add new translation files for multiple languages ([d884b61](https://github.com/LindemannRock/craft-formie-rest-api/commit/d884b61d4a8c33e4cb4c24535f6c7b29a0c4324e))
* **translations:** add new translations for Formie REST API strings in multiple languages ([7471a83](https://github.com/LindemannRock/craft-formie-rest-api/commit/7471a8347997219c1a80fce2712bd9ab6562ace2))
* **translations:** update plugin name descriptions in multiple languages ([278c13a](https://github.com/LindemannRock/craft-formie-rest-api/commit/278c13a78c4464882fa5e9676a294e015e7565c4))


### Bug Fixes

* **api:** cast count results to integer for accurate pagination ([65d3d8d](https://github.com/LindemannRock/craft-formie-rest-api/commit/65d3d8df1c893b26fd36106e929d7f0ec113516c))
* drop PAT requirement for release-please — use built-in GITHUB_TOKEN ([29ba64a](https://github.com/LindemannRock/craft-formie-rest-api/commit/29ba64aa896c07a57f00e5d76f401acfbdaff29f))
* **translations:** correct Danish, Spanish, Norwegian, and Swedish plugin name translations ([a7bfa63](https://github.com/LindemannRock/craft-formie-rest-api/commit/a7bfa63bd9befba480910bb0b94d47d044a6fe6d))

## [3.3.0](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.2.2...v3.3.0) - 2026-04-02


### Features

* **svg:** add new icon mask SVG file and replace existing icon ([c20e01c](https://github.com/LindemannRock/craft-formie-rest-api/commit/c20e01ced74ffb6729567da259bdca299c8e4b22))

## [3.2.2](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.2.1...v3.2.2) - 2026-03-04


### Bug Fixes

* **models:** require pluginName in Settings model validation ([f7726ad](https://github.com/LindemannRock/craft-formie-rest-api/commit/f7726adaef03932c38d9bc9bb27ddd3bd8883b6e))
* **services:** replace getenv with App::env for API key retrieval ([1f9214e](https://github.com/LindemannRock/craft-formie-rest-api/commit/1f9214e1d8f3e22ab864aa6e3cd3ccd82b0a9b5e))


### Miscellaneous Chores

* add .gitattributes with export-ignore for Packagist distribution ([17a84bc](https://github.com/LindemannRock/craft-formie-rest-api/commit/17a84bc9801833881355270d35c0964bef58e3de))
* switch to Craft License for commercial release ([0359a81](https://github.com/LindemannRock/craft-formie-rest-api/commit/0359a81d6c3b6fbd672f7109ac57ceebfde245ec))

## [3.2.1](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.2.0...v3.2.1) - 2026-02-07


### Miscellaneous Chores

* release 3.2.1 ([db79319](https://github.com/LindemannRock/craft-formie-rest-api/commit/db79319857f8eb7f311d325e1ca289cc728de222))

## [3.2.0](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.1.0...v3.2.0) - 2026-01-11


### Features

* bootstrap base plugin helper in FormieRestApi initialization ([9150a5e](https://github.com/LindemannRock/craft-formie-rest-api/commit/9150a5e6d3258dfc9c126bacf199d4b4c3730a8c))


### Bug Fixes

* update Settings model to improve type handling and refactor plugin handle method ([fe1dd6e](https://github.com/LindemannRock/craft-formie-rest-api/commit/fe1dd6eabf40dcca45016d50c5ef52d59334f700))


### Miscellaneous Chores

* update composer.json to include ECS in require-dev section ([af26f85](https://github.com/LindemannRock/craft-formie-rest-api/commit/af26f85c8f13e691e4fb929d29d34e385c1ff5ad))

## [3.1.0](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.0.3...v3.1.0) - 2025-12-04


### Features

* add PHPStan and ECS configurations, and include Postman collections for Al Hatab Foods API ([ad24852](https://github.com/LindemannRock/craft-formie-rest-api/commit/ad248521dfd605256200bdd8e06275cba3880a26))


### Bug Fixes

* improve documentation for plugin properties and ensure newline at end of file ([17d5144](https://github.com/LindemannRock/craft-formie-rest-api/commit/17d51446eb8e907fd1b53b2fc4aadd4e7da384cb))

## [3.0.3](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.0.2...v3.0.3) - 2025-11-01


### Bug Fixes

* update validation method names for consistency and improve formatting in settings ([4a47057](https://github.com/LindemannRock/craft-formie-rest-api/commit/4a47057496ab2a8034404e6e2a0942ef826ae5c4))

## [3.0.2](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.0.1...v3.0.2) - 2025-10-27


### Miscellaneous Chores

* update .gitignore ([31e2ba7](https://github.com/LindemannRock/craft-formie-rest-api/commit/31e2ba71da69222f32d7607f3b50c61f72474a46))

## [3.0.1](https://github.com/LindemannRock/craft-formie-rest-api/compare/v3.0.0...v3.0.1) - 2025-10-20


### Miscellaneous Chores

* update README with additional badges ([434ffe7](https://github.com/LindemannRock/craft-formie-rest-api/commit/434ffe7a09ece1c2926b12ea77ca9482e111c172))

## [3.0.0](https://github.com/LindemannRock/craft-formie-rest-api/compare/v1.0.4...v3.0.0) - 2025-10-20


### Miscellaneous Chores

* bump version scheme to match Formie 3 ([c531f2f](https://github.com/LindemannRock/craft-formie-rest-api/commit/c531f2fb4ad778f7df832db681a6e91a4fe4b68c))

## [1.0.4](https://github.com/LindemannRock/craft-formie-rest-api/compare/v1.0.3...v1.0.4) - 2025-10-16


### Bug Fixes

* update installation instructions for Composer and DDEV ([3256296](https://github.com/LindemannRock/craft-formie-rest-api/commit/32562968f738a1f03f5e5d42d97e81e60961d452))

## [1.0.3](https://github.com/LindemannRock/craft-formie-rest-api/compare/v1.0.2...v1.0.3) - 2025-10-16


### Bug Fixes

* change license from proprietary to MIT in composer.json ([0d62507](https://github.com/LindemannRock/craft-formie-rest-api/commit/0d62507f223cced4b6e4769b10dcdc8fa34374d7))

## [1.0.2](https://github.com/LindemannRock/craft-formie-rest-api/compare/v1.0.1...v1.0.2) - 2025-10-16


### Bug Fixes

* update author details and add RSS feed link in composer.json ([78d5a0c](https://github.com/LindemannRock/craft-formie-rest-api/commit/78d5a0cc74d5eab472f546cb9571d050b292d906))

## [1.0.1](https://github.com/LindemannRock/craft-formie-rest-api/compare/v1.0.0...v1.0.1) - 2025-09-24


### Bug Fixes

* update repository references and improve .gitignore structure ([9abb42f](https://github.com/LindemannRock/craft-formie-rest-api/commit/9abb42f6945a1bcda1f2a4cb5949ed3d2f27903e))

## 1.0.0 - 2025-09-15


### Features

* initial Formie REST API plugin implementation ([393d29f](https://github.com/LindemannRock/formie-rest-api/commit/393d29f3814a438c1f9447db95007e232f1a08c9))
