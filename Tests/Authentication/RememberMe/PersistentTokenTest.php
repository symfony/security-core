<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Tests\Authentication\RememberMe;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentToken;

class PersistentTokenTest extends TestCase
{
    public function testConstructor()
    {
        $lastUsed = new \DateTimeImmutable();
        $token = new PersistentToken('fooclass', 'fooname', 'fooseries', 'footokenvalue', $lastUsed);

        $this->assertEquals('fooclass', $token->getClass());
        $this->assertEquals('fooname', $token->getUserIdentifier());
        $this->assertEquals('fooseries', $token->getSeries());
        $this->assertEquals('footokenvalue', $token->getTokenValue());
        $this->assertEquals($lastUsed, $token->getLastUsed());
    }

    public function testDateTime()
    {
        $lastUsed = new \DateTime();
        $token = new PersistentToken('fooclass', 'fooname', 'fooseries', 'footokenvalue', $lastUsed);

        $this->assertEquals($lastUsed, $token->getLastUsed());
    }
}
