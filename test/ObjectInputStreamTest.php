<?php

class ObjectInputStreamTest extends PHPUnit\Framework\TestCase {
    public function testReadMagic() {
        $buffer = pack("n", 0xACED);

        $objectInputStream = new ObjectInputStream($buffer);

        $objectInputStream->readMagic();
    }

    /**
     * @expectedException Exception
     */
    public function testReadMagicError() {
        $buffer = pack("n", 0xACEA);

        $objectInputStream = new ObjectInputStream($buffer);

        $objectInputStream->readMagic();
    }

    public function testReadStreamVersion() {
        $buffer = pack("n", 0x0005);

        $objectInputStream = new ObjectInputStream($buffer);

        $objectInputStream->readStreamVersion();
    }

    /**
     * @expectedException Exception
     */
    public function testReadStreamVersionError() {
        $buffer = pack("n", 0x0006);

        $objectInputStream = new ObjectInputStream($buffer);

        $objectInputStream->readStreamVersion();
    }

    public function testReadHeader() {
        $buffer = pack("nn", 0xACED, 0x0005);

        $objectInputStream = new ObjectInputStream($buffer);

        $objectInputStream->readHeader();
    }

    public function testReadObject() {
        /*
         * Object(new Cls0):
         *  000000000: ac ed 00 05 73 72 00 18 74 65 73 74 2e 73 65 72 | ﾬ￭ sr test.ser
         *  000000010: 69 61 6c 69 7a 65 2e 4d 61 69 6e 24 43 6c 73 30 | ialize.Main$Cls0
         *  000000020: aa b5 c9 3c f1 76 72 5d 02 00 01 4a 00 05 76 61 | ﾪﾵ￉<￱vr] J va
         *  000000030: 6c 75 65 78 70 00 00 00 00 00 00 01 e5          | luexp      ￥
         */

        $buffer =
            hex2bin(
                "aced000573720018746573742e736572".
                "69616c697a652e4d61696e24436c7330".
                "aab5c93cf176725d0200014a00057661".
                "6c7565787000000000000001e5"
            );

        $objectInputStream = new ObjectInputStream($buffer);

        $object = $objectInputStream->readObject();

        var_dump($object);
    }
}