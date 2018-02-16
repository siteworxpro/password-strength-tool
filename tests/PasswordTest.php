<?php

final class PasswordTest extends \PHPUnit\Framework\TestCase
{

    public function testReturnsPasswordClass(): void
    {
        $password = Siteworx\Passwords\Scorer::score('APassword');

        $this->assertInstanceOf(Siteworx\Passwords\Scorer::class, $password);
    }

    /**
     * @expectedException \LogicException
     */
    public function testThrowsExceptionPositive(): void
    {
        $password = Siteworx\Passwords\Scorer::score('APassword', 6);
    }

    /**
     * @expectedException \LogicException
     */
    public function testThrowsExceptionNegative(): void
    {
        $password = Siteworx\Passwords\Scorer::score('APassword', -6);
    }

    public function testVeryPoorCommonPasswords(): void
    {
        $veryPoorPasswords = [
            'aaaaaaaaaaaaaabbbbbcccccddddeeeeeeeeeeeeeffffffffffffggggggggg',
            'asdfgzxcv',
            'P@ssword',
            '111111111111222222222223333333333444455556666777888899999'
        ];

        foreach ($veryPoorPasswords as $poorPassword) {
            $password = \Siteworx\Passwords\Scorer::score($poorPassword);

            $this->assertTrue($password->isVeryPoor());
            $this->assertFalse($password->isPoor());
            $this->assertFalse($password->isFair());
            $this->assertFalse($password->isStrong());
            $this->assertFalse($password->isVeryStrong());
            $this->assertFalse($password->isExcellent());
        }

    }

    public function testPoorPasswords(): void
    {
        $poorPasswords = [
            'P@ssword!',
            'qwerasdfzxcv',
            '1qaz2wsx3edc'
        ];

        foreach ($poorPasswords as $poorPassword) {
            $password = \Siteworx\Passwords\Scorer::score($poorPassword);

            $this->assertFalse($password->isVeryPoor());
            $this->assertTrue($password->isPoor());
            $this->assertFalse($password->isFair());
            $this->assertFalse($password->isStrong());
            $this->assertFalse($password->isVeryStrong());
            $this->assertFalse($password->isExcellent());
        }
    }

    public function testFairPasswords(): void
    {
        $fairPasswords = [
            'p2vNGcbnjq79',
            'N2GLVE8TwMh3',
            'W4JR5Y21eDgS'
        ];

        foreach ($fairPasswords as $fairPassword) {
            $password = \Siteworx\Passwords\Scorer::score($fairPassword);

            $this->assertFalse($password->isVeryPoor());
            $this->assertFalse($password->isPoor());
            $this->assertTrue($password->isFair());
            $this->assertFalse($password->isStrong());
            $this->assertFalse($password->isVeryStrong());
            $this->assertFalse($password->isExcellent());
        }
    }

    public function testStrongPasswords(): void
    {
        $strongPasswords = [
            '0}UVsHlMwWF^21.Q',
            '9]y.GqzxsoaX8142',
            '~Va.^i(,\FLt=eurHzg@W'
        ];

        foreach ($strongPasswords as $strongPasswords) {
            $password = \Siteworx\Passwords\Scorer::score($strongPasswords);

            $this->assertFalse($password->isVeryPoor());
            $this->assertFalse($password->isPoor());
            $this->assertFalse($password->isFair());
            $this->assertTrue($password->isStrong());
            $this->assertFalse($password->isVeryStrong());
            $this->assertFalse($password->isExcellent());
        }
    }

    public function testVeryStrongPasswords(): void
    {
        $veryStrongPasswords = [
            '3+gk~X7m!aUe6JG=chzI',
            ',=45Dxv#M)n(\}uLpsF.',
            'y7eJ5dDIH$N)#3}KMsT%'
        ];

        foreach ($veryStrongPasswords as $veryStrongPassword) {
            $password = \Siteworx\Passwords\Scorer::score($veryStrongPassword);

            $this->assertFalse($password->isVeryPoor());
            $this->assertFalse($password->isPoor());
            $this->assertFalse($password->isFair());
            $this->assertFalse($password->isStrong());
            $this->assertTrue($password->isVeryStrong());
            $this->assertFalse($password->isExcellent());
        }
    }

    public function testExcellentPasswords(): void
    {
        $excellentPasswords = [
            '8B^2IFjN[n&ryOETRA4#1!tHe0=',
            'cxbt0[YQsaw%!k#+)2LUgu?drBD',
            '{9eOSN$JB!`\K3sH7*8m]rRU&xf'
        ];

        foreach ($excellentPasswords as $excellentPassword) {
            $password = \Siteworx\Passwords\Scorer::score($excellentPassword);

            $this->assertFalse($password->isVeryPoor());
            $this->assertFalse($password->isPoor());
            $this->assertFalse($password->isFair());
            $this->assertFalse($password->isStrong());
            $this->assertFalse($password->isVeryStrong());
            $this->assertTrue($password->isExcellent());
        }
    }

    public function testBiasUp(): void
    {
        $fairPasswords = [
            'p2vNGacbfnjq79',
            'N2GLVwE8TwMdh3',
            'W4dJR5Y21eaDgS'
        ];

        foreach ($fairPasswords as $fairPassword) {
            $password = \Siteworx\Passwords\Scorer::score($fairPassword);

            $this->assertFalse($password->isVeryPoor());
            $this->assertFalse($password->isPoor());
            $this->assertTrue($password->isFair());
            $this->assertFalse($password->isStrong());
            $this->assertFalse($password->isVeryStrong());
            $this->assertFalse($password->isExcellent());
        }

        foreach ($fairPasswords as $strongNowPassword) {
            $password = \Siteworx\Passwords\Scorer::score($strongNowPassword, 4);

            $this->assertFalse($password->isVeryPoor());
            $this->assertFalse($password->isPoor());
            $this->assertFalse($password->isFair());
            $this->assertTrue($password->isStrong());
            $this->assertFalse($password->isVeryStrong());
            $this->assertFalse($password->isExcellent());
        }

        foreach ($fairPasswords as $veryStrongNowPassword) {
            $password = \Siteworx\Passwords\Scorer::score($veryStrongNowPassword, 5);

            $this->assertFalse($password->isVeryPoor());
            $this->assertFalse($password->isPoor());
            $this->assertFalse($password->isFair());
            $this->assertFalse($password->isStrong());
            $this->assertTrue($password->isVeryStrong());
            $this->assertFalse($password->isExcellent());
        }
    }


    public function testBiasDown(): void
    {
        $fairPasswords = [
            'p2vNGacbfnjq79',
            'N2GLVwE8TwMdh3',
            'W4dJR5Y21eaDgS'
        ];

        foreach ($fairPasswords as $fairPassword) {
            $password = \Siteworx\Passwords\Scorer::score($fairPassword);

            $this->assertFalse($password->isVeryPoor());
            $this->assertFalse($password->isPoor());
            $this->assertTrue($password->isFair());
            $this->assertFalse($password->isStrong());
            $this->assertFalse($password->isVeryStrong());
            $this->assertFalse($password->isExcellent());
        }

        foreach ($fairPasswords as $poorNowPassword) {
            $password = \Siteworx\Passwords\Scorer::score($poorNowPassword, -4);

            $this->assertFalse($password->isVeryPoor());
            $this->assertTrue($password->isPoor());
            $this->assertFalse($password->isFair());
            $this->assertFalse($password->isStrong());
            $this->assertFalse($password->isVeryStrong());
            $this->assertFalse($password->isExcellent());
        }

        foreach ($fairPasswords as $veryPoorNowPassword) {
            $password = \Siteworx\Passwords\Scorer::score($veryPoorNowPassword, -5);

            $this->assertTrue($password->isVeryPoor());
            $this->assertFalse($password->isPoor());
            $this->assertFalse($password->isFair());
            $this->assertFalse($password->isStrong());
            $this->assertFalse($password->isVeryStrong());
            $this->assertFalse($password->isExcellent());
        }
    }


}