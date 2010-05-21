SimpleCASBundle
---------------

This bundle integrates [SimpleCAS](http://code.google.com/p/simplecas/) into
[Symfony 2](http://github.com/symfony/symfony), which allows users to be
authenticated against a [CAS server](http://www.jasig.org/cas). 

## Installation

### PEAR Dependencies ###

This bundle depends on SimpleCAS, which is installable via PEAR:

    $ pear channel-discover simplecas.googlecode.com/svn
    $ pear install simplecas/SimpleCAS-alpha

SimpleCAS depends on HTTP_Request2, so you may have to install that if PEAR does
not handle the dependency on its own.

### Application Kernel ###

Add SimpleCASBundle to the `registerBundles()` method of your application kernel:

    [php]
    public function registerBundles()
    {
        return array(
            new Bundle\SimpleCASBundle\Bundle(),
        );
    }

### Class Autoloading ###

Since this bundle depends on PEAR libraries, add their prefixes to either the
root or project-level `autoload.php` file:

    [php]
    $loader->registerPrefixes(array(
        'HTTP_'      => '/usr/share/php',
        'SimpleCAS_' => '/usr/share/php',
        'SimpleCAS'  => '/usr/share/php',
    ));

The above example assumes that the PEAR libraries were installed to `/usr/share/php`
and may need to be modified.  At present, `UniversalClassLoader` does not support
autoloading of files within PHP's include path, so the PEAR path must be explicitly
defined.
