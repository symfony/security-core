<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Tests\Authorization;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\User\InMemoryUser;

class ExpressionLanguageTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testIsAuthenticated($token, $expression, $result)
    {
        $expressionLanguage = new ExpressionLanguage();
        $trustResolver = new AuthenticationTrustResolver();
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);
        $accessDecisionManager = new AccessDecisionManager([new RoleVoter(), new AuthenticatedVoter($trustResolver)]);
        $authChecker = new AuthorizationChecker($tokenStorage, $accessDecisionManager);

        $context = [];
        $context['auth_checker'] = $authChecker;
        $context['token'] = $token;

        $this->assertEquals($result, $expressionLanguage->evaluate($expression, $context));
    }

    public static function provider()
    {
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];
        $user = new InMemoryUser('username', 'password', $roles);

        $noToken = null;
        $rememberMeToken = new RememberMeToken($user, 'firewall-name', 'firewall');
        $usernamePasswordToken = new UsernamePasswordToken($user, 'firewall-name', $roles);

        return [
            [$noToken, 'is_authenticated()', false],
            [$noToken, 'is_fully_authenticated()', false],
            [$noToken, 'is_remember_me()', false],

            [$rememberMeToken, 'is_authenticated()', true],
            [$rememberMeToken, 'is_fully_authenticated()', false],
            [$rememberMeToken, 'is_remember_me()', true],
            [$rememberMeToken, "is_granted('ROLE_FOO')", false],
            [$rememberMeToken, "is_granted('ROLE_USER')", true],

            [$usernamePasswordToken, 'is_authenticated()', true],
            [$usernamePasswordToken, 'is_fully_authenticated()', true],
            [$usernamePasswordToken, 'is_remember_me()', false],
            [$usernamePasswordToken, "is_granted('ROLE_FOO')", false],
            [$usernamePasswordToken, "is_granted('ROLE_USER')", true],
        ];
    }
}
