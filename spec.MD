Java serialization specification
Source: https://docs.oracle.com/javase/8/docs/platform/serialization/spec/protocol.html

stream:
  magic version contents

contents:
  content
  contents content

content:
  object
  blockdata

object => TAG + objectSpec
  [TC_OBJECT] newObject
  [TC_CLASS] newClass
  [TC_ARRAY] newArray
  [TC_STRING] newString
  [TC_LOMG_STRING] newString
  [TC_ENUM] newEnum
  [TC_CLASSDESC] newClassDesc
  [TC_PROXYCLASSDESC] newClassDesc
  [TC_REFERENCE] prevObject
  [TC_NULL] nullReference
  [TC_EXCEPTION] exception
  [TC_RESET]

newClass:
  TC_CLASS classDesc newHandle

classDesc => TAG + classDesc:
  [TC_CLASSDESC] newClassDesc
  [TC_PROXYCLASSDESC] newClassDesc
  [TC_NULL] nullReference
  [TC_REFERENCE] prevObject      // !!!an object required to be of type ClassDesc

superClassDesc:
  classDesc

newClassDesc:
  TC_CLASSDESC className serialVersionUID newHandle classDescInfo
  TC_PROXYCLASSDESC newHandle proxyClassDescInfo

classDescInfo:
  classDescFlags fields classAnnotation superClassDesc

className:
  (utf)

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

newArray:
  TC_ARRAY classDesc newHandle (int)<size> values[size]

newObject:
  TC_OBJECT classDesc newHandle classdata[]  // data for each class

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

newString:
  TC_STRING newHandle (utf)
  TC_LONGSTRING newHandle (long-utf)

newEnum:
  TC_ENUM classDesc newHandle enumConstantName

enumConstantName:
  (String)object

prevObject
  TC_REFERENCE (int)handle

nullReference
  TC_NULL

exception:
  TC_EXCEPTION reset (Throwable)object	 reset

magic:
  STREAM_MAGIC

version
  STREAM_VERSION
 values:          // The size and types are described by the
                 // classDesc for the current object
 newHandle:       // The next number in sequence is assigned
                 // to the object being serialized or deserialized
 reset:           // The set of known objects is discarded
                 // so the objects of the exception do not
                 // overlap with the previously sent objects
                 // or with objects that may be sent after
                 // the exception
