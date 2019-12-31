<?php

namespace Lampager\Cake\Test\TestCase;

use Cake\TestSuite\TestCase as BaseTestCase;

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
        return $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual), $message);
    }
}
