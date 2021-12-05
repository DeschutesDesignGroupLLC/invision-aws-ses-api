# CHANGELOG
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.0.11] - 2021-12-05
### Fixed
- Fixed error when trying to change pages within bounce and complaint logs.

## [1.0.10] - 2021-10-19
### Added
- Can now set a domain as a verified identity.
- Added a from column on the outgoing logs.
### Fixed
- Bounce and Complaint logs now properly show the email in question rather than the member's current email.

## [1.0.9] - 2021-09-21
### Added
- Support for non-ASCII characters.

## [1.0.8] - 2021-09-09
### Added
- Support for PHP 8.

## [1.0.7] - 2021-08-29
### Added
- All verified identities will now need to be saved in your Invision Power Board ACP settings. This will help the application with determining the appropriate sending email address to use.

## [1.0.6] - 2021-08-08
### Fixed
- FROM name is now double-quoted to comply with RFC 3696 which enables support for ASCII graphic characters in your FROM name.

## [1.0.5] - 2021-07-13
### Fixed
- API Manager now properly parses SNS notifications for Bounce and Complaint Management.
- Saved settings are now formatted correctly.

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