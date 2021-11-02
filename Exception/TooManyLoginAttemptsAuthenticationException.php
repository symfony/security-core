<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Exception;

/**
 * This exception is thrown if there where too many failed login attempts in
 * this session.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class TooManyLoginAttemptsAuthenticationException extends AuthenticationException
{
    private ?int $threshold;

    public function __construct(int $threshold = null)
    {
        $this->threshold = $threshold;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageData(): array
    {
        return [
            '%minutes%' => $this->threshold,
            '%count%' => (int) $this->threshold,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'Too many failed login attempts, please try again '.($this->threshold ? 'in %minutes% minute'.($this->threshold > 1 ? 's' : '').'.' : 'later.');
    }

    /**
     * {@inheritdoc}
     */
    public function __serialize(): array
    {
        return [$this->threshold, parent::__serialize()];
    }

    /**
     * {@inheritdoc}
     */
    public function __unserialize(array $data): void
    {
        [$this->threshold, $parentData] = $data;
        $parentData = \is_array($parentData) ? $parentData : unserialize($parentData);
        parent::__unserialize($parentData);
    }
}
