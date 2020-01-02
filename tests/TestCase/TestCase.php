<?php

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
     * @param  string                                                       $message
     * @return void
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function assertJsonEquals($expected, $actual, $message = '')
    {
        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual), $message);
    }

    /**
     * Asserts that two given SQL query statements are equal.
     *
     * @param  string $expected
     * @param  string $actual
     * @param  string $message
     * @return void
     */
    public function assertSqlEquals($expected, $actual, $message = '')
    {
        $formatter = new Formatter();
        $this->assertSame($formatter->format($expected), $formatter->format($actual), $message);
    }
}
