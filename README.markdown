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