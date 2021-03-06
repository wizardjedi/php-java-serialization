# Java serialization specification
Source: https://docs.oracle.com/javase/8/docs/platform/serialization/spec/protocol.html

This document created to clearify Java serialization specification. Rules reformated for more comfortable implementation process.

```
stream:
  magic version contents

magic:
  STREAM_MAGIC = 0xAC 0xED

version:
  STREAM_VERSION = 0x00 0x05
```

Stream starts with MAGIC (ACED in hex) constant and stream version (nowadays its 5) and then content of stream.

```
contents:
  content
  contents content
```
Content of stream is one or more content objects.

```
content:
  object
  blockdata
```
Content object could be object or block data.

```
object => TAG + objectSpec
  [TC_OBJECT] newObject
  [TC_CLASS] newClass
  [TC_ARRAY] newArray
  [TC_STRING] newString             // newString rule 2-byte for length
  [TC_LONG_STRING] newString        // newString rule 8-bytes for length
  [TC_ENUM] newEnum
  [TC_CLASSDESC] newClassDesc       // classDesc rule
  [TC_PROXYCLASSDESC] newClassDesc  // classDesc rule
  [TC_REFERENCE] prevObject         // classDesc rule
  [TC_NULL] nullReference           // classDesc rule
  [TC_EXCEPTION] exception
  [TC_RESET]                        // maybe in exception?
```
Object represented by a several rules. For determine rule every rule contains 1-byte tag value that represent rule.
So for parsing you have to read 1-byte tag value and then determine rule by this tag value.

```
newObject:
  TC_OBJECT classDesc newHandle classdata[]  // data for each class

newClass:
  TC_CLASS classDesc newHandle

newArray:
  TC_ARRAY classDesc newHandle (int)<size> values[size]

newString:
  TC_STRING newHandle (utf)
  TC_LONGSTRING newHandle (long-utf)

newEnum:
  TC_ENUM classDesc newHandle enumConstantName

classDesc => TAG + classDesc:
  [TC_CLASSDESC] newClassDesc
  [TC_PROXYCLASSDESC] newClassDesc
  [TC_NULL] nullReference
  [TC_REFERENCE] prevObject      // !!!an object required to be of type ClassDesc

newClassDesc:
  TC_CLASSDESC className serialVersionUID newHandle classDescInfo
  TC_PROXYCLASSDESC newHandle proxyClassDescInfo

prevObject
  TC_REFERENCE (int)handle

nullReference:
  TC_NULL

exception:
  TC_EXCEPTION reset (Throwable)object	 reset // ? maybe TC_RESET ?

superClassDesc:
  classDesc

classDescInfo:
  classDescFlags fields classAnnotation superClassDesc

className:
  (utf)

(utf):
(long-utf):
    // Note that the symbol (utf) is used to designate a string written using 2-byte (unsigned short) length information, and (long-utf) is used to designate a string written using 8-byte length (signed long) information.

serialVersionUID:
  (long)

classDescFlags:
  (byte)                  // Defined in Terminal Symbols and
                            // Constants
proxyClassDescInfo:
  (int)<count> proxyInterfaceName[count] classAnnotation
      superClassDesc

proxyInterfaceName:
   (utf)

fields:
  (short)<count>  fieldDesc[count]

fieldDesc:
  primitiveDesc
  objectDesc

primitiveDesc:
  prim_typecode fieldName

objectDesc:
  obj_typecode fieldName className1

fieldName:
  (utf)

className1:
  (String)object             // String containing the field's type,
                             // in field descriptor format

classAnnotation:
  endBlockData
  contents endBlockData      // contents written by annotateClass

prim_typecode:
  `B'	// byte
  `C'	// char
  `D'	// double
  `F'	// float
  `I'	// integer
  `J'	// long
  `S'	// short
  `Z'	// boolean

obj_typecode:
  `[`	// array
  `L'	// object

classdata:
  nowrclass                 // SC_SERIALIZABLE & classDescFlag &&
                            // !(SC_WRITE_METHOD & classDescFlags)
  wrclass objectAnnotation  // SC_SERIALIZABLE & classDescFlag &&
                            // SC_WRITE_METHOD & classDescFlags
  externalContents          // SC_EXTERNALIZABLE & classDescFlag &&
                            // !(SC_BLOCKDATA  & classDescFlags
  objectAnnotation          // SC_EXTERNALIZABLE & classDescFlag&&
                            // SC_BLOCKDATA & classDescFlags

nowrclass:
  values                    // fields in order of class descriptor

wrclass:
  nowrclass

objectAnnotation:
  endBlockData
  contents endBlockData     // contents written by writeObject
                            // or writeExternal PROTOCOL_VERSION_2.

blockdata:
  blockdatashort
  blockdatalong

blockdatashort:
  TC_BLOCKDATA (unsigned byte)<size> (byte)[size]

blockdatalong:
  TC_BLOCKDATALONG (int)<size> (byte)[size]

endBlockData	:
  TC_ENDBLOCKDATA

externalContent:          // Only parseable by readExternal
  ( bytes)                // primitive data
    object

externalContents:         // externalContent written by
  externalContent         // writeExternal in PROTOCOL_VERSION_1.
  externalContents externalContent

enumConstantName:
  (String)object

values:          // The size and types are described by the
                 // classDesc for the current object
newHandle:       // The next number in sequence is assigned
                 // to the object being serialized or deserialized
reset:           // The set of known objects is discarded
                 // so the objects of the exception do not
                 // overlap with the previously sent objects
                 // or with objects that may be sent after
                 // the exception
```
