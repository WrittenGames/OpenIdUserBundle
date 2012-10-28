WGOpenIdUserBundle
==================

The fantastic FOSUserBundle.

The convenient FpOpenIdBundle.

The amazing Doctrine.

Bam! Smashed together, stir-fried for a bit, then left to simmer on a Symfony
project. Quick, painless, no bother at all.

If you want this bundle to be a bit more flexible, I'm accepting Pull Requests
as of now...

The WGOpenIdUserBundle combines the FOSUserBundle with the FpOpenIdBundle.
It can be pretty much dropped in and it works without much configuration,
all you need to do is to extend the User class of your choice (currently
Doctrine ORM and MongoDB).

It provides 3rd party authentication via OpenID, offers a user entity with
ID, username, email address, enabled, locked, lastLogin, createdAt and roles
fields, and ties into the group feature of the FOSUserBundle.

**Caution:** This bundle is *not* developed in sync with Symfony's repository.
The current version seems to work reasonably well with Symfony 2.1 and 2.2.

Documentation
-------------

The bulk of the documentation is stored in the `Resources/doc/index.md`
file in this bundle:

[Read the Documentation for master](https://github.com/WrittenGames/OpenIdUserBundle/blob/master/Resources/doc/index.md)

Installation
------------

All the installation instructions are located in the [documentation](https://github.com/WrittenGames/OpenIdUserBundle/blob/master/Resources/doc/index.md).

License
-------

This bundle is under the MIT license.

About
-----

WGOpenIdUserBundle is a [Written Games](https://github.com/WrittenGames) project.

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/WrittenGames/OpenIdUserBundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.
