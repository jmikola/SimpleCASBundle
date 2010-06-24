<?php

namespace Bundle\SimpleCASBundle\Helper;

use Symfony\Components\Templating\Helper\Helper;
use Bundle\SimpleCASBundle\SimpleCAS;

/**
 * SimpleCASHelper acts as a proxy for getter methods on the SimpleCAS client
 * object, which allows for convenient access from within a template.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCASHelper extends Helper
{
    /**
     * SimpleCAS client methods that this helper supports.
     *
     * @var array
     */
    protected $proxiedMethods = array(
        'isAuthenticated',
        'getAuthenticatedUid',
        'getAuthenticatedUser',
        'getLoginUrl',
        'getLogoutUrl',
    );

    /**
     * SimpleCAS client instance.
     *
     * @var SimpleCAS
     */
    protected $simplecas;

    /**
     * Constructor.
     *
     * @param SimpleCAS $simplecas
     * @return SimpleCASHelper
     */
    public function __construct(SimpleCAS $simplecas)
    {
        $this->simplecas = $simplecas;
    }

    /**
     * Catches getter methods to 
     *
     * @param string $method    The called method name
     * @param array  $arguments The method arguments
     * @return mixed
     * @throws \BadMethodCallException When calling an undefined getter method
     */
    public function __call($method, $arguments)
    {
        if (!in_array($method, $this->proxiedMethods)) {
            throw new \BadMethodCallException(sprintf('Call to unsupported method: %s', $method));
        }

        return call_user_func_array(array($this->simplecas, $method), $arguments);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string
     */
    public function getName()
    {
        return 'simplecas';
    }
}
