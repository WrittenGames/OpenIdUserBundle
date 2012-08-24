Getting Started With WGOpenIdUserBundle
=======================================

The fantastic FOSUserBundle.

The convenient FpOpenIdBundle.

The amazing Doctrine ORM.

Bam! Smashed together, stir fried for a bit, then left to rest on a Symfony 2.1
project. Quick, painless, no bother at all - if the ingredients are what you feel like.

If you want this bundle to be a bit more flexible, I'm accepting Pull Requests
as of now...

## Prerequisites

This version of the bundle requires Symfony 2.1.

## Installation

Installation is a reasonably quick 6 step process:

1. Download WGOpenIdUserBundle and its dependencies using composer
2. Enable the bundle
3. Configure your application's security.yml
4. Import FOSUserBundle and FpOpenIdBundle default configuration
5. Import routing files
6. Update your database schema

### Step 1: Download WGOpenIdUserBundle and its dependencies using composer

Add WGOpenIdUserBundle in your composer.json:

```js
{
    "require": {
        "writtengames/openid-user-bundle": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update writtengames/openid-user-bundle
```

Composer will install the bundle(s) to your project's `vendor` directory, along
with the FOSUserBundle and/or the FpOpenIdBundle if required.

### Step 2: Enable the bundle

Enable the bundle(s) in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        // enable the bundle itself
        new WG\OpenIdUserBundle\WGOpenIdUserBundle(),
        // enabled its dependencies if not already done
        new FOS\UserBundle\FOSUserBundle(),
        new Fp\OpenIdBundle\FpOpenIdBundle(),
    );
}
```

### Step 3: Configure your application's security.yml

In order for Symfony's security component to use the WGOpenIdUserBundle, you must
tell it to do so in the `security.yml` file. The `security.yml` file is where the
basic configuration for the security for your application is contained.

Below is the minimal configuration necessary to use the WGOpenIdUserBundle in
your application:

``` yaml
# app/config/security.yml

security:
    providers:
        wg_user_manager:
            id: wg.openid.user_manager

    firewalls:
        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            pattern:    ^/
            anonymous:  true
            logout:     true
            fp_openid:
                login_path:                 /login_openid
                create_user_if_not_exists:  true
                provider:                   wg_user_manager
                required_attributes:        [ namePerson/first, namePerson/last, contact/email ]

    access_control:
        - { path: ^/secured_area, role: ROLE_USER }
        - { path: ^/login_openid$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/, role: IS_AUTHENTICATED_ANONYMOUSLY }

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: ROLE_ADMIN
```

### Step 4: Import FOSUserBundle and FpOpenIdBundle default configuration

Now that you have properly configured your application's `security.yml` to work
with the WGOpenIdUserBundle, the next step is to tell it the bundle's default
configuration of the FOSUserBundle and the FpOpenIdBundle.

Add the following import directive to your `config.yml`:

``` yaml
# app/config/config.yml

imports:
    - { resource: "@WGOpenIdUserBundle/Resources/config/config.yml" }
```

Or don't, and configure those two bundles yourself.

### Step 5: Import routing files

Now that you have activated and configured the bundle, all that is left to do is
import the routing directives.

``` yaml
# app/config/routing.yml

openiduser:
    resource: "@WGOpenIdUserBundle/Resources/config/routing.yml"
    prefix:   /
```

Or don't, and configure the routes yourself.

### Step 6: Update your database schema

Now that the bundle is configured, the last thing you need to do is update your
database schema because the bundle has added three new entities - a user class,
a group class and an OpenID identity class.

For ORM run the following command.

``` bash
$ php app/console doctrine:schema:update --force
```

### Next Steps

For anything (a lot of things) not covered in this documentation, please refer
to the documentation of the FOSUserBundle, the FpOpenIdBundle, and Symfony.
