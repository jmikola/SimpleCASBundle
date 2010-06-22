# SimpleCASBundle

This bundle integrates [SimpleCAS](http://code.google.com/p/simplecas/) into
[Symfony 2](http://github.com/symfony/symfony), which allows users to be
authenticated against a [CAS server](http://www.jasig.org/cas).

## Installation

### PEAR Dependencies

This bundle depends on SimpleCAS, which can be installed via PEAR:

    $ pear channel-discover simplecas.googlecode.com/svn
    $ pear install simplecas/SimpleCAS-alpha

SimpleCAS depends on [HTTP_Request2](http://pear.php.net/package/HTTP_Request2),
which itself depends on [Net_URL2](http://pear.php.net/package/Net_URL2).  You
may have to install these packages manually if PEAR does not handle the dependency
on its own.

Alternatively, a [SimpleCAS git repository](http://github.com/jmikola/simplecas) is
available, which contains a patch for logout service redirection support.

### Application Kernel

Add SimpleCASBundle to the `registerBundles()` method of your application kernel:

    public function registerBundles()
    {
        return array(
            new Bundle\SimpleCASBundle\Bundle(),
        );
    }

### Class Autoloading

Since this bundle depends on PEAR libraries for dependency injection, their
prefixes should be added to the project-level `autoload.php` file:

    $loader->registerPrefixes(array(
        'HTTP_'      => '/usr/share/php',
        'SimpleCAS_' => '/usr/share/php',
    ));

The above example assumes that the PEAR libraries were installed to `/usr/share/php`.
It may be more convenient to place these libraries in the `vendor/` path of your
project:

    $loader->registerPrefixes(array(
        'HTTP_'      => __DIR__ . '/vendor/pear',
        'SimpleCAS_' => __DIR__ . '/vendor/simplecas',
    ));

## Configuration

### Application config.yml

Enable loading of the SimpleCAS service by adding the following to the application's
`config.yml` file:

    simplecas.simplecas: ~

This will enable the service using the default parameters defined in `simplecas.xml`.
An example of more specific configuration follows: 

    simplecas.simplecas:
      protocol:
        hostname: cas-server.example.com:8443
        uri:      cas
        request:
          method: GET
          config:
            adapter:         curl
            ssl_verify_peer: true
            ssl_cafile:      /etc/ssl/certs/tomcat-cas.pem

See also:

 * [HTTP_Request2 config documentation](http://pear.php.net/manual/en/package.http.http-request2.config.php)
 * [HTTP_Request2 adapter documentation](http://pear.php.net/manual/en/package.http.http-request2.adapters.php)

### Logout Service Redirection

By default, the logout page for a CAS server renders a link to whatever value is
passed as the "url" query string parameter.  This is standard behavior for the
SimpleCAS library as well.

For cases where you would rather have your CAS server immediately redirect to a
URL after logging out, CAS allows a `followServiceRedirects` property to be set
in the XML configuration for `LogoutController`.  This option will check for a
"service" query string parameter and redirect to its value after processing the
logout request.

Support for this feature was added in the [SimpleCAS git repository](http://github.com/jmikola/simplecas),
and SimpleCASBundle also has built-in support for the option:

    simplecas.simplecas:
      protocol:
        logout_service_redirect: true

See also:

 * http://tp.its.yale.edu/pipermail/cas/2008-August/009508.html

### Database Adapter

Typically, you will end up using the principal identifier for the authenticated
user to fetch a user object from the database.  SimpleCASBundle supports this
using a database adapters, and the `SimpleCAS` class has two methods to faciliate
fetching user objects for the authenticated principal.

#### Doctrine ODM MongoDB

The Doctrine ODM MongoDB adapter may be configured as:

    simplecas.simplecas:
      adapter:
        name: doctrine.odm.mongodb
        options:
          document_name:    Application\ApplicationBundle\Entities\User
          principal_field:  _id

The above example will use the default document manager for ODM and attempt to
match the principal identifier from CAS to the `_id` field on the given document.
Both `document_name` and `principal_field` are required options.  An optional
`document_manager` option exists to request a specific document manager by name.