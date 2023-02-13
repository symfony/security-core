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

use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * AuthenticationException is the base class for all authentication exceptions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
#[WithHttpStatus(401)]
class AuthenticationException extends RuntimeException
{
    /** @internal */
    protected $serialized;

    private ?TokenInterface $token = null;

    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        unset($this->serialized);
        parent::__construct($message, $code, $previous);
    }

    public function getToken(): ?TokenInterface
    {
        return $this->token;
    }

    /**
     * @return void
     */
    public function setToken(TokenInterface $token)
    {
        $this->token = $token;
    }

    /**
     * Returns all the necessary state of the object for serialization purposes.
     *
     * There is no need to serialize any entry, they should be returned as-is.
     * If you extend this method, keep in mind you MUST guarantee parent data is present in the state.
     * Here is an example of how to extend this method:
     * <code>
     *     public function __serialize(): array
     *     {
     *         return [$this->childAttribute, parent::__serialize()];
     *     }
     * </code>
     *
     * @see __unserialize()
     */
    public function __serialize(): array
    {
        return [$this->token, $this->code, $this->message, $this->file, $this->line];
    }

    /**
     * Restores the object state from an array given by __serialize().
     *
     * There is no need to unserialize any entry in $data, they are already ready-to-use.
     * If you extend this method, keep in mind you MUST pass the parent data to its respective class.
     * Here is an example of how to extend this method:
     * <code>
     *     public function __unserialize(array $data): void
     *     {
     *         [$this->childAttribute, $parentData] = $data;
     *         parent::__unserialize($parentData);
     *     }
     * </code>
     *
     * @see __serialize()
     */
    public function __unserialize(array $data): void
    {
        [$this->token, $this->code, $this->message, $this->file, $this->line] = $data;
    }

    /**
     * Message key to be used by the translation component.
     *
     * @return string
     */
    public function getMessageKey()
    {
        return 'An authentication exception occurred.';
    }

    /**
     * Message data to be used by the translation component.
     */
    public function getMessageData(): array
    {
        return [];
    }

    /**
     * @internal
     */
    public function __sleep(): array
    {
        $this->serialized = $this->__serialize();

        return ['serialized'];
    }

    /**
     * @internal
     */
    public function __wakeup(): void
    {
        $this->__unserialize($this->serialized);
        unset($this->serialized);
    }
}
