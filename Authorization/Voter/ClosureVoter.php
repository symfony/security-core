<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * This voter allows using a closure as the attribute being voted on.
 *
 * The following named arguments are passed to the closure:
 *
 * - `token`: The token being used for voting
 * - `subject`: The subject of the vote
 * - `accessDecisionManager`: The access decision manager
 * - `trustResolver`: The trust resolver
 *
 * @see IsGranted doc for the complete closure signature.
 *
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
final class ClosureVoter implements CacheableVoterInterface
{
    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
        private AuthenticationTrustResolverInterface $trustResolver,
    ) {
    }

    public function supportsAttribute(string $attribute): bool
    {
        return false;
    }

    public function supportsType(string $subjectType): bool
    {
        return true;
    }

    public function vote(TokenInterface $token, mixed $subject, array $attributes, ?Vote $vote = null): int
    {
        $vote ??= new Vote();
        $failingClosures = [];
        $result = VoterInterface::ACCESS_ABSTAIN;
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof \Closure) {
                continue;
            }

            $name = (new \ReflectionFunction($attribute))->name;
            $result = VoterInterface::ACCESS_DENIED;
            if ($attribute(token: $token, subject: $subject, accessDecisionManager: $this->accessDecisionManager, trustResolver: $this->trustResolver)) {
                $vote->reasons[] = \sprintf('Closure %s returned true.', $name);

                return VoterInterface::ACCESS_GRANTED;
            }

            $failingClosures[] = $name;
        }

        if ($failingClosures) {
            $vote->reasons[] = \sprintf('Closure%s %s returned false.', 1 < \count($failingClosures) ? 's' : '', implode(', ', $failingClosures));
        }

        return $result;
    }
}
