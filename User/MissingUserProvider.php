<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\User;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * MissingUserProvider is a dummy user provider used to throw proper exception
 * when a firewall requires a user provider but none was defined.
 *
 * @internal
 *
 * @template-implements UserProviderInterface<never>
 */
class MissingUserProvider implements UserProviderInterface
{
    /**
     * @param string $firewall the firewall missing a provider
     */
    public function __construct(string $firewall)
    {
        throw new InvalidConfigurationException(sprintf('"%s" firewall requires a user provider but none was defined.', $firewall));
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        throw new \BadMethodCallException();
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new \BadMethodCallException();
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new \BadMethodCallException();
    }

    public function supportsClass(string $class): bool
    {
        throw new \BadMethodCallException();
    }
}
