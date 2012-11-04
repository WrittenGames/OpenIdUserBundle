Getting Started With WGOpenIdUserBundle
=======================================

## Prerequisites

This version of the bundle requires Symfony 2.1 or higher.

## Installation (TODO: instructions need adapting after making it storage agnostic)

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
            logout:
                path:                       /openid/logout
            fp_openid:
                login_path:                 /openid/login
                check_path:                 /openid/login_check
                create_user_if_not_exists:  true
                provider:                   wg_user_manager
                required_attributes:        [ contact/email, namePerson, namePerson/first, namePerson/last ]

    access_control:
        - { path: ^/secured_area, role: ROLE_USER }
        - { path: ^/openid$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/, role: IS_AUTHENTICATED_ANONYMOUSLY }

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: ROLE_ADMIN
```

### Step 4: Configuration

Now that you have properly configured your application's `security.yml` to work
with the WGOpenIdUserBundle, the next step is to configure it. The WGOpenIdUserBundle
configures its dependencies in a YAML file using parameters, so add the following
to your `config.yml`:

``` yaml
# app/config/config.yml

imports:
    - { resource: "@WGOpenIdUserBundle/Resources/config/bundleconfig.yml" }

parameters:
    wg_openid.firewall_name: main
    wg_openid.db_driver: orm
    wg_openid.identity_class: Acme\UserBundle\Entity\Identity
    wg_openid.user_class: Acme\UserBundle\Entity\User
    wg_openid.group_class: Acme\UserBundle\Entity\Group
```

Please note that when extending the UserIdentity entity, the $user property must
be mapped using fetch=EAGER mode, otherwise the User object will not be properly
refreshed.

### Step 5: Import routing files

Now that you have activated and configured the bundle, all that is left to do is
import the routing directives.

``` yaml
# app/config/routing.yml

openiduser_identities:
    resource: "@WGOpenIdUserBundle/Resources/config/routing/identity.yml"
    prefix:   /openid

openiduser_users:
    resource: "@WGOpenIdUserBundle/Resources/config/routing/user.yml"
    prefix:   /people

openiduser_groups:
    resource: "@WGOpenIdUserBundle/Resources/config/routing/group.yml"
    prefix:   /groups

openiduser_admin:
    resource: "@WGOpenIdUserBundle/Resources/config/routing/admin.yml"
    prefix:   /admin
```

Or don't, and configure all the routes yourself. Your choice.

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
