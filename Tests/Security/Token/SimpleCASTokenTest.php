<?php

namespace Bundle\SimpleCASBundle\Tests\Component\Security\Authentication\Token;

use Bundle\SimpleCASBundle\Security\Token\SimpleCASToken;
use Symfony\Component\Security\Role\Role;

class SimpleCASTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorWithoutRoles()
    {
        $token = new SimpleCASToken('foo', 'bar');
        $this->assertSame('foo', $token->getUser());
        $this->assertSame('bar', $token->getCredentials());
        $this->assertFalse($token->isAuthenticated());
        $this->assertEmpty($token->getRoles());
    }

    public function testConstructorWithRoles()
    {
        $token = new SimpleCASToken('foo', 'bar', array('ROLE_FOO'));
        $this->assertSame('foo', $token->getUser());
        $this->assertSame('bar', $token->getCredentials());
        $this->assertTrue($token->isAuthenticated());
        $this->assertEquals(array(new Role('ROLE_FOO')), $token->getRoles());
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetAuthenticatedTrueThrowsException()
    {
        $token = new SimpleCASToken('foo', 'bar');
        $token->setAuthenticated(true);
    }

    public function testSetAuthenticatedFalse()
    {
        $token = new SimpleCASToken('foo', 'bar');
        $token->setAuthenticated(false);
        $this->assertFalse($token->isAuthenticated());
    }

    public function testEraseCredentials()
    {
        $token = new SimpleCASToken('foo', 'bar');
        $token->eraseCredentials();
        $this->assertNull($token->getCredentials());
    }
}
