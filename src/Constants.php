<?php

class Constants {
    const LENGTH_MAGIC = 2;
    const LENGTH_STREAM_VERSION = 2;
    const LENGTH_BYTE = 1;
    const LENGTH_SHORT = 2;
    const LENGTH_INT = 4;
    const LENGTH_LONG = 8;
    const LENGTH_CHAR = 2;
    const LENGTH_FLOAT = 4;
    const LENGTH_DOUBLE = 8;

    // The following symbols in java.io.ObjectStreamConstants
    // define the terminal and constant values expected in a stream.
    const STREAM_MAGIC = 0xACED;
    const STREAM_VERSION = 5;
    const TC_NULL = 0x70;
    const TC_REFERENCE = 0x71;
    const TC_CLASSDESC = 0x72;
    const TC_OBJECT = 0x73;
    const TC_STRING = 0x74;
    const TC_ARRAY = 0x75;
    const TC_CLASS = 0x76;
    const TC_BLOCKDATA = 0x77;
    const TC_ENDBLOCKDATA = 0x78;
    const TC_RESET = 0x79;
    const TC_BLOCKDATALONG = 0x7A;
    const TC_EXCEPTION = 0x7B;
    const TC_LONGSTRING =  0x7C;
    const TC_PROXYCLASSDESC =  0x7D;
    const TC_ENUM =  0x7E;
    const baseWireHandle = 0x7E0000;

    // The flag byte classDescFlags may include values of
    const SC_WRITE_METHOD = 0x01; //if SC_SERIALIZABLE
    const SC_BLOCK_DATA = 0x08;    //if SC_EXTERNALIZABLE
    const SC_SERIALIZABLE = 0x02;
    const SC_EXTERNALIZABLE = 0x04;
    const SC_ENUM = 0x10;

    const PRIM_TYPE_CODE_BYTE = 'B';
    const PRIM_TYPE_CODE_CHAR = 'C';
    const PRIM_TYPE_CODE_DOUBLE = 'D';
    const PRIM_TYPE_CODE_FLOAT = 'F';
    const PRIM_TYPE_CODE_INTEGER = 'I';
    const PRIM_TYPE_CODE_LONG = 'J';
    const PRIM_TYPE_CODE_SHORT = 'S';
    const PRIM_TYPE_CODE_BOOLEAN = 'Z';

    const OBJECT_TYPE_CODE_ARRAY = '[';
    const OBJECT_TYPE_CODE_OBJECT = 'L';

    public static $PRIMITIVE_TYPE_CODES = array(
        Constants::PRIM_TYPE_CODE_BYTE,
        Constants::PRIM_TYPE_CODE_CHAR,
        Constants::PRIM_TYPE_CODE_DOUBLE,
        Constants::PRIM_TYPE_CODE_FLOAT,
        Constants::PRIM_TYPE_CODE_INTEGER,
        Constants::PRIM_TYPE_CODE_LONG,
        Constants::PRIM_TYPE_CODE_SHORT,
        Constants::PRIM_TYPE_CODE_BOOLEAN,
    );

    public static $OBJECT_TYPE_CODES = array(
        Constants::OBJECT_TYPE_CODE_ARRAY,
        Constants::OBJECT_TYPE_CODE_OBJECT,
    );
}