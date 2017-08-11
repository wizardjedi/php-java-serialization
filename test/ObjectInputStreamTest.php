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

    /**
     * Test simple object with 4 primitive fields
     * JAVA CODE:
     *  public static class Cls0 implements Serializable {
     *      protected long value0 = -485;
     *      protected long value1 = 123;
     *      protected int value3 = -12;
     *      protected int value4 = 1023;
     *  }
     *
     */
    public function testReadObject() {
        /*
         * Object(new Cls0):
         *  000000000: ac ed 00 05 73 72 00 18 74 65 73 74 2e 73 65 72 | ﾬ￭ sr test.ser
         *  000000010: 69 61 6c 69 7a 65 2e 4d 61 69 6e 24 43 6c 73 30 | ialize.Main$Cls0
         *  000000020: 35 cd 5b d2 ca 8f 2d fc 02 00 04 4a 00 06 76 61 | 5ￍ[ￒￊﾏ-￼ J va
         *  000000030: 6c 75 65 30 4a 00 06 76 61 6c 75 65 31 49 00 06 | lue0J value1I 
         *  000000040: 76 61 6c 75 65 33 49 00 06 76 61 6c 75 65 34 78 | value3I value4x
         *  000000050: 70 ff ff ff ff ff ff fe 1b 00 00 00 00 00 00 00 | p￿￿￿￿￿￿�
         *  000000060: 7b ff ff ff f4 00 00 03 ff                      | {￿￿￿￴  ￿
         */

        $buffer =
            hex2bin(
                "aced000573720018746573742e736572".
                "69616c697a652e4d61696e24436c7330".
                "35cd5bd2ca8f2dfc0200044a00067661".
                "6c7565304a000676616c756531490006".
                "76616c75653349000676616c75653478".
                "70fffffffffffffe1b00000000000000".
                "7bfffffff4000003ff"
            );

        $objectInputStream = new ObjectInputStream($buffer);

        $object = $objectInputStream->readObject();

        $this->assertEquals("test\serialize\Main\Cls0", get_class($object));

        $this->assertEquals(-485, $object->value0);
        $this->assertEquals(123, $object->value1);
        $this->assertEquals(-12, $object->value3);
        $this->assertEquals(1023, $object->value4);
    }


    /**
     * JAVA CODE:
     *  public static class PrimitiveClass implements Serializable {
     *      protected byte value00 = -12;
     *      protected byte value01 = 12;
     *
     *      protected char value10 = 'a';
     *      protected char value11 = 'Я';
     *
     *      protected short value20 = -485;
     *      protected short value21 = 485;
     *
     *      protected int value30 = -485;
     *      protected int value31 = 485;
     *
     *      protected long value40 = -485;
     *      protected long value41 = 485;
     *
     *      protected float value50 = -485.123f;
     *      protected float value51 = 485.123f;
     *
     *      protected double value60 = -485.123;
     *      protected double value61 = 485.123;
     *  }
     */
    public function testPrimitiveClass() {
        /*
         *  Object(new PrimitiveClass):
         *  000000000: ac ed 00 05 73 72 00 22 74 65 73 74 2e 73 65 72 | ﾬ￭ sr "test.ser
         *  000000010: 69 61 6c 69 7a 65 2e 4d 61 69 6e 24 50 72 69 6d | ialize.Main$Prim
         *  000000020: 69 74 69 76 65 43 6c 61 73 73 19 9a 97 89 f6 ad | itiveClassﾚﾗﾉ￶ﾭ
         *  000000030: 0c 99 02 00 0e 42 00 07 76 61 6c 75 65 30 30 42 | ﾙ B value00B
         *  000000040: 00 07 76 61 6c 75 65 30 31 43 00 07 76 61 6c 75 |  value01C valu
         *  000000050: 65 31 30 43 00 07 76 61 6c 75 65 31 31 53 00 07 | e10C value11S 
         *  000000060: 76 61 6c 75 65 32 30 53 00 07 76 61 6c 75 65 32 | value20S value2
         *  000000070: 31 49 00 07 76 61 6c 75 65 33 30 49 00 07 76 61 | 1I value30I va
         *  000000080: 6c 75 65 33 31 4a 00 07 76 61 6c 75 65 34 30 4a | lue31J value40J
         *  000000090: 00 07 76 61 6c 75 65 34 31 46 00 07 76 61 6c 75 |  value41F valu
         *  0000000a0: 65 35 30 46 00 07 76 61 6c 75 65 35 31 44 00 07 | e50F value51D 
         *  0000000b0: 76 61 6c 75 65 36 30 44 00 07 76 61 6c 75 65 36 | value60D value6
         *  0000000c0: 31 78 70 f4 0c 00 61 04 2f fe 1b 01 e5 ff ff fe | 1xp￴ a/�￥￿￿�
         *  0000000d0: 1b 00 00 01 e5 ff ff ff ff ff ff fe 1b 00 00 00 |   ￥￿￿￿￿￿￿�
         *  0000000e0: 00 00 00 01 e5 c3 f2 8f be 43 f2 8f be c0 7e 51 |    ￥ￃ￲ﾏﾾC￲ﾏﾾ￀~Q
         *  0000000f0: f7 ce d9 16 87 40 7e 51 f7 ce d9 16 87          | ￷ￎ￙ﾇ@~Q￷ￎ￙ﾇ
         */

        $buffer =
            hex2bin(
                "aced000573720022746573742e736572".
                "69616c697a652e4d61696e245072696d".
                "6974697665436c617373199a9789f6ad".
                "0c9902000e42000776616c7565303042".
                "000776616c7565303143000776616c75".
                "65313043000776616c75653131530007".
                "76616c7565323053000776616c756532".
                "3149000776616c756533304900077661".
                "6c756533314a000776616c756534304a".
                "000776616c7565343146000776616c75".
                "65353046000776616c75653531440007".
                "76616c7565363044000776616c756536".
                "317870f40c0061042ffe1b01e5fffffe".
                "1b000001e5fffffffffffffe1b000000".
                "00000001e5c3f28fbe43f28fbec07e51".
                "f7ced91687407e51f7ced91687"
            );

        $objectInputStream = new ObjectInputStream($buffer);

        $object = $objectInputStream->readObject();

        $this->assertEquals("test\serialize\Main\PrimitiveClass", get_class($object));

        $this->assertEquals(-12, $object->value00);
        $this->assertEquals( 12, $object->value01);

        $this->assertEquals('a', $object->value10);
        $this->assertEquals('Я', $object->value11);

        $this->assertEquals(-485, $object->value20);
        $this->assertEquals( 485, $object->value21);

        $this->assertEquals(-485, $object->value30);
        $this->assertEquals( 485, $object->value31);

        $this->assertEquals(-485, $object->value40);
        $this->assertEquals( 485, $object->value41);

        $this->assertEquals(-485.123, $object->value50,'Float value incorrect', 0.001);
        $this->assertEquals( 485.123, $object->value51,'Float value incorrect', 0.001);

        $this->assertEquals(-485.123, $object->value60);
        $this->assertEquals( 485.123, $object->value61);
    }

    /**
     * JAVA CODE:

     */
    public function testPrimitiveInheritedClass() {
        /*
         * Object(new Cls2):
         *  000000000: ac ed 00 05 73 72 00 18 74 65 73 74 2e 73 65 72 | ﾬ￭ sr test.ser
         *  000000010: 69 61 6c 69 7a 65 2e 4d 61 69 6e 24 43 6c 73 32 | ialize.Main$Cls2
         *  000000020: 5b 34 53 26 2c d5 84 5a 02 00 01 4c 00 04 76 61 | [4S&,ￕﾄZ L va
         *  000000030: 6c 32 74 00 12 4c 6a 61 76 61 2f 6c 61 6e 67 2f | l2t Ljava/lang/
         *  000000040: 53 74 72 69 6e 67 3b 78 72 00 18 74 65 73 74 2e | String;xr test.
         *  000000050: 73 65 72 69 61 6c 69 7a 65 2e 4d 61 69 6e 24 43 | serialize.Main$C
         *  000000060: 6c 73 31 4d 22 c3 b4 41 00 bf 41 02 00 01 4c 00 | ls1M"ￃﾴA ﾿A L
         *  000000070: 03 76 61 6c 74 00 10 4c 6a 61 76 61 2f 6c 61 6e | valt Ljava/lan
         *  000000080: 67 2f 4c 6f 6e 67 3b 78 70 73 72 00 0e 6a 61 76 | g/Long;xpsr jav
         *  000000090: 61 2e 6c 61 6e 67 2e 4c 6f 6e 67 3b 8b e4 90 cc | a.lang.Long;ﾋ￤ﾐￌ
         *  0000000a0: 8f 23 df 02 00 01 4a 00 05 76 61 6c 75 65 78 72 | ﾏ#￟ J valuexr
         *  0000000b0: 00 10 6a 61 76 61 2e 6c 61 6e 67 2e 4e 75 6d 62 |  java.lang.Numb
         *  0000000c0: 65 72 86 ac 95 1d 0b 94 e0 8b 02 00 00 78 70 00 | erﾆﾬﾕﾔ￠ﾋ  xp
         *  0000000d0: 00 00 00 00 00 00 7b 74 00 0b 54 65 73 74 20 73 |       {t Test s
         *  0000000e0: 74 72 69 6e 67                                  | tring
         */

        $buffer =
            hex2bin(
                "aced000573720018746573742e736572".
                "69616c697a652e4d61696e24436c7332".
                "5b3453262cd5845a0200014c00047661".
                "6c327400124c6a6176612f6c616e672f".
                "537472696e673b78720018746573742e".
                "73657269616c697a652e4d61696e2443".
                "6c73314d22c3b44100bf410200014c00".
                "0376616c7400104c6a6176612f6c616e".
                "672f4c6f6e673b78707372000e6a6176".
                "612e6c616e672e4c6f6e673b8be490cc".
                "8f23df0200014a000576616c75657872".
                "00106a6176612e6c616e672e4e756d62".
                "657286ac951d0b94e08b020000787000".
                "0000000000007b74000b546573742073".
                "7472696e67"
            );

        $objectInputStream = new ObjectInputStream($buffer);

        $objectInputStream->setLogEnabled(true);

        try {
            $object = $objectInputStream->readObject();
        } catch (Exception $e) {
            echo $e->getTraceAsString();

            throw $e;
        }
        var_dump($object);
    }
}