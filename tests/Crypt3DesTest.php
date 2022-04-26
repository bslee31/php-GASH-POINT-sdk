<?php

namespace GashPoint\Tests;

use GashPoint\Crypt3Des;
use PHPUnit\Framework\TestCase;

class Crypt3DesTest extends TestCase
{
    protected $key = 'yrafRg1C9mHOP/IU09JaP5S9GekAyESG';
    protected $iv = 'UNvI+LOQQRI=';

    /**
     * @return void
     */
    public function testInstantiatingWithoutKeyThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Crypt3Des('', $this->iv);
    }

    /**
     * @return void
     */
    public function testInstantiatingWithoutIvThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Crypt3Des($this->key, '');
    }

    /**
     * @return void
     */
    public function testEncrypt(): void
    {
        $crypt3Des = new Crypt3Des($this->key, $this->iv);
        $str = $crypt3Des->encrypt('testEncrypt()');
        $this->assertEquals('gPsUZ1j2fSVtErzC+jmfdQ==', $str);
    }
}