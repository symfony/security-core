<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Tests\Encoder;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;

/**
 * @author Elnur Abdurrakhimov <elnur@elnur.pro>
 * @group legacy
 */
class NativePasswordEncoderTest extends TestCase
{
    public function testCostBelowRange()
    {
        $this->expectException(\InvalidArgumentException::class);
        new NativePasswordEncoder(null, null, 3);
    }

    public function testCostAboveRange()
    {
        $this->expectException(\InvalidArgumentException::class);
        new NativePasswordEncoder(null, null, 32);
    }

    /**
     * @dataProvider validRangeData
     */
    public function testCostInRange($cost)
    {
        $this->assertInstanceOf(NativePasswordEncoder::class, new NativePasswordEncoder(null, null, $cost));
    }

    public static function validRangeData()
    {
        $costs = range(4, 31);
        array_walk($costs, function (&$cost) { $cost = [$cost]; });

        return $costs;
    }

    public function testValidation()
    {
        $encoder = new NativePasswordEncoder();
        $result = $encoder->encodePassword('password', null);
        $this->assertTrue($encoder->isPasswordValid($result, 'password', null));
        $this->assertFalse($encoder->isPasswordValid($result, 'anotherPassword', null));
        $this->assertFalse($encoder->isPasswordValid($result, '', null));
    }

    public function testNonArgonValidation()
    {
        $encoder = new NativePasswordEncoder();
        $this->assertTrue($encoder->isPasswordValid('$5$abcdefgh$ZLdkj8mkc2XVSrPVjskDAgZPGjtj1VGVaa1aUkrMTU/', 'password', null));
        $this->assertFalse($encoder->isPasswordValid('$5$abcdefgh$ZLdkj8mkc2XVSrPVjskDAgZPGjtj1VGVaa1aUkrMTU/', 'anotherPassword', null));
        $this->assertTrue($encoder->isPasswordValid('$6$abcdefgh$yVfUwsw5T.JApa8POvClA1pQ5peiq97DUNyXCZN5IrF.BMSkiaLQ5kvpuEm/VQ1Tvh/KV2TcaWh8qinoW5dhA1', 'password', null));
        $this->assertFalse($encoder->isPasswordValid('$6$abcdefgh$yVfUwsw5T.JApa8POvClA1pQ5peiq97DUNyXCZN5IrF.BMSkiaLQ5kvpuEm/VQ1Tvh/KV2TcaWh8qinoW5dhA1', 'anotherPassword', null));
    }

    public function testConfiguredAlgorithm()
    {
        $encoder = new NativePasswordEncoder(null, null, null, \PASSWORD_BCRYPT);
        $result = $encoder->encodePassword('password', null);
        $this->assertTrue($encoder->isPasswordValid($result, 'password', null));
        $this->assertStringStartsWith('$2', $result);
    }

    public function testConfiguredAlgorithmWithLegacyConstValue()
    {
        $encoder = new NativePasswordEncoder(null, null, null, '1');
        $result = $encoder->encodePassword('password', null);
        $this->assertTrue($encoder->isPasswordValid($result, 'password', null));
        $this->assertStringStartsWith('$2', $result);
    }

    public function testCheckPasswordLength()
    {
        $encoder = new NativePasswordEncoder(null, null, 4);
        $result = password_hash(str_repeat('a', 72), \PASSWORD_BCRYPT, ['cost' => 4]);

        $this->assertFalse($encoder->isPasswordValid($result, str_repeat('a', 73), 'salt'));
        $this->assertTrue($encoder->isPasswordValid($result, str_repeat('a', 72), 'salt'));
    }

    public function testNeedsRehash()
    {
        $encoder = new NativePasswordEncoder(4, 11000, 4);

        $this->assertTrue($encoder->needsRehash('dummyhash'));

        $hash = $encoder->encodePassword('foo', 'salt');
        $this->assertFalse($encoder->needsRehash($hash));

        $encoder = new NativePasswordEncoder(5, 11000, 5);
        $this->assertTrue($encoder->needsRehash($hash));
    }
}
