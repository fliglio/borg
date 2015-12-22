<?php
namespace Fliglio\Borg\Type;


class PrimitiveTest extends \PHPUnit_Framework_TestCase {


	public function testMarshallingSymmetry() {
		// given
		$entity = new Primitive("hello world");

		// when
		$vo = $entity->marshal();
		$out = Primitive::unmarshal($vo);

		// then
		$this->assertEquals($entity, $out, "unmarshalled primitive should be same as original");
	}
	
	public function testMarshallingSymmetryArray() {
		// given
		$entity = new Primitive([1, 2, "foo", "bar"]);

		// when
		$vo = $entity->marshal();
		$out = Primitive::unmarshal($vo);

		// then
		$this->assertEquals($entity, $out, "unmarshalled primitive should be same as original");
	}
	
	public function testMarshallingSymmetryNull() {
		// given
		$entity = new Primitive(null);

		// when
		$vo = $entity->marshal();
		$out = Primitive::unmarshal($vo);

		// then
		$this->assertEquals($entity, $out, "unmarshalled primitive should be same as original");
	}
	
}

