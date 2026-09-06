<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Tests\User;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\OAuth2User;

class OAuth2UserTest extends TestCase
{
    public function testCannotCreateUserWithoutSubProperty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The claim "sub" or "username" must be provided.');

        new OAuth2User();
    }

    /**
     * RFC 7662 §2.2 makes "aud" "a service-specific string identifier or list of string
     * identifiers", so the introspection response of a token minted for several resource servers
     * has to build a user just as well.
     */
    public function testCreateUserWithSeveralAudiences()
    {
        $user = new OAuth2User(sub: 'Z5O3upPC88QrAjx00dis', aud: ['https://protected.example.net/resource', 'https://other.example.net']);

        $this->assertSame(['https://protected.example.net/resource', 'https://other.example.net'], $user->aud);
    }

    public function testCreateFullUserWithAdditionalClaimsUsingPositionalParameters()
    {
        $this->assertEquals(new OAuth2User(
            scope: 'read write dolphin',
            username: 'jdoe',
            exp: 1419356238,
            iat: 1419350238,
            sub: 'Z5O3upPC88QrAjx00dis',
            aud: 'https://protected.example.net/resource',
            iss: 'https://server.example.com/',
            client_id: 'l238j323ds-23ij4',
            extension_field: 'twenty-seven'
        ), new OAuth2User(...[
            'client_id' => 'l238j323ds-23ij4',
            'username' => 'jdoe',
            'scope' => 'read write dolphin',
            'sub' => 'Z5O3upPC88QrAjx00dis',
            'aud' => 'https://protected.example.net/resource',
            'iss' => 'https://server.example.com/',
            'exp' => 1419356238,
            'iat' => 1419350238,
            'extension_field' => 'twenty-seven',
        ]));
    }
}
