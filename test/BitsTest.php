<?php

class BitsTest extends PHPUnit\Framework\TestCase {
    public function testReadByte() {
        $this->assertEquals(-12, Bits::readSigned(hex2bin("f4")));
        $this->assertEquals( 12, Bits::readSigned(hex2bin("0c")));
    }

    public function testReadChar() {
        $this->assertEquals('a', Bits::readChar(hex2bin("0061")));
        $this->assertEquals('Я', Bits::readChar(hex2bin("042f")));
    }

    public function testReadFloat() {
        $this->assertEquals(-485.123, Bits::readFloat(hex2bin("c3f28fbe")),'Float number inccorect',0.001);
        $this->assertEquals( 485.123, Bits::readFloat(hex2bin("43f28fbe")),'Float number inccorect',0.001);
    }

    public function testReadDouble() {
        $this->assertEquals(-485.123, Bits::readDouble(hex2bin("c07e51f7ced91687")));
        $this->assertEquals( 485.123, Bits::readDouble(hex2bin("407e51f7ced91687")));
    }
}