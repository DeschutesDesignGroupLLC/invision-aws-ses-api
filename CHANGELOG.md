# CHANGELOG
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.0.3] - 2021-05-29
### Added
- Ready for 4.6.
### Fixed
- Emails with CC and BCC now properly send. This bug was seen within the email copies setting in the Commerce application.

## [1.0.2] - 2021-01-03
### Changed
- Vendor dependencies now loaded with getRootPath function.

## [1.0.1] - 2020-12-23
### Changed
- Moved dependencies from interface folder to sources.

## [1.0.0] - 2020-12-18
### Added
- Secret key is now encrypted when storing in DB.
### Changed
- Updated log templates.
- Updated search log function.

## [1.0.0-beta.2] - 2020-12-17
### Added
- Added a message to the top of the settings to alert the user over the email override.
### Changed
- Region field is now required.
- Secret key is now protected using the Password Form Helper.

## [1.0.0-beta.1] - 2020-12-15
### Added
- Initial beta release.