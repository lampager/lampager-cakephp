<?php

declare(strict_types=1);

namespace Lampager\Cake\Test\TestCase;

use Cake\TestSuite\TestCase as BaseTestCase;
use NilPortugues\Sql\QueryFormatter\Formatter;

abstract class TestCase extends BaseTestCase
{
    /**
     * Asserts that two given JSON encoded objects or arrays are equal.
     *
     * @param  mixed                                                        $expected
     * @param  mixed                                                        $actual
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function assertJsonEquals($expected, $actual, string $message = ''): void
    {
        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual), $message);
    }

    /**
     * Asserts that two given SQL query statements are equal.
     */
    public function assertSqlEquals(string $expected, string $actual, string $message = ''): void
    {
        $formatter = new Formatter();
        $this->assertSame($formatter->format($expected), $formatter->format($actual), $message);
    }
}
