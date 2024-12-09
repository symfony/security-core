<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Tests\Authorization\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\ClosureVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ClosureVoterTest extends TestCase
{
    private ClosureVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new ClosureVoter(
            $this->createMock(AccessDecisionManagerInterface::class),
            $this->createMock(AuthenticationTrustResolverInterface::class),
        );
    }

    public function testEmptyAttributeAbstains()
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote(
            $this->createMock(TokenInterface::class),
            null,
            [])
        );
    }

    public function testClosureReturningFalseDeniesAccess()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getRoleNames')->willReturn([]);
        $token->method('getUser')->willReturn($this->createMock(UserInterface::class));

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote(
            $token,
            null,
            [fn (...$vars) => false]
        ));
    }

    public function testClosureReturningTrueGrantsAccess()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getRoleNames')->willReturn([]);
        $token->method('getUser')->willReturn($this->createMock(UserInterface::class));

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote(
            $token,
            null,
            [fn (...$vars) => true]
        ));
    }

    public function testArgumentsContent()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getRoleNames')->willReturn(['MY_ROLE', 'ANOTHER_ROLE']);
        $token->method('getUser')->willReturn($this->createMock(UserInterface::class));

        $outerSubject = new \stdClass();

        $this->voter->vote(
            $token,
            $outerSubject,
            [function (...$vars) use ($outerSubject) {
                $this->assertInstanceOf(TokenInterface::class, $vars['token']);
                $this->assertSame($outerSubject, $vars['subject']);

                $this->assertInstanceOf(AccessDecisionManagerInterface::class, $vars['accessDecisionManager']);
                $this->assertInstanceOf(AuthenticationTrustResolverInterface::class, $vars['trustResolver']);

                return true;
            }]
        );
    }
}
