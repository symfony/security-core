<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Tests\Authentication\Token;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\User\InMemoryUser;

class RememberMeTokenTest extends TestCase
{
    public function testConstructor()
    {
        $user = $this->getUser();
        $token = new RememberMeToken($user, 'fookey');

        $this->assertEquals('fookey', $token->getFirewallName());
        $this->assertEquals(['ROLE_FOO'], $token->getRoleNames());
        $this->assertSame($user, $token->getUser());
    }

    #[IgnoreDeprecations]
    #[Group('legacy')]
    public function testSecret()
    {
        $user = $this->getUser();
        $token = new RememberMeToken($user, 'fookey', 'foo');

        $this->assertEquals('foo', $token->getSecret());
    }

    #[IgnoreDeprecations]
    #[Group('legacy')]
    public function testUnserializeRejectsLegacyStringParentData()
    {
        $token = new RememberMeToken($this->getUser(), 'fookey', 'foo');
        $data = $token->__serialize();
        $data[2] = serialize($data[2]);

        $token = new RememberMeToken($this->getUser(), 'fookey', 'foo');

        $this->expectException(\TypeError::class);
        $token->__unserialize($data);
    }

    protected function getUser($roles = ['ROLE_FOO'])
    {
        return new InMemoryUser('John', 'password', $roles);
    }
}
