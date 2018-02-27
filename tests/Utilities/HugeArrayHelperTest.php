<?php namespace Decahedron\Tests\Vulcan\Utilities;

use Decahedron\Tests\Vulcan\TestCase;
use Decahedron\Vulcan\Utilities\HugeArrayHelper;

class HugeArrayHelperTest extends TestCase
{
    public function test_it_can_set()
    {
        $a = [];
        HugeArrayHelper::set($a, 'foo', 'val');
        $this->assertEquals(['foo' => 'val'], $a);

        $a = [];
        HugeArrayHelper::set($a, 'foo.bar', 'val');
        $this->assertEquals(['foo' => ['bar' => 'val']], $a);

        $a = [];
        HugeArrayHelper::set($a, 'foo.[].bar', 'val');
        $this->assertEquals(['foo' => [['bar' => 'val']]], $a);
        HugeArrayHelper::set($a, 'foo.[].bar', 'val');
        $this->assertEquals(['foo' => [['bar' => 'val']]], $a);

        $a = [];
        HugeArrayHelper::set($a, 'foo.[]', 'val');
        $this->assertEquals(['foo' => ['val']], $a);

        $a = [];
        HugeArrayHelper::set($a, 'foo.[].bar.baz.[]', 'val');
        $this->assertEquals(['foo' => [['bar' => ['baz' => ['val']]]]], $a);
        HugeArrayHelper::set($a, 'foo.[].bar.baz.[]', 'val');
        $this->assertEquals(['foo' => [['bar' => ['baz' => ['val']]]]], $a);

        // check edge case with mixed array types
        $a = [];
        HugeArrayHelper::set($a, 'foo.bar', 'val');
        $this->assertEquals(['foo' => ['bar' => 'val']], $a);
        HugeArrayHelper::set($a, 'foo.[]', 'val');
        $this->assertEquals(['foo' => ['val']], $a);

        $a = [];
        HugeArrayHelper::set($a, 'foo.bar.baz', 'val');
        $this->assertEquals(['foo' => ['bar' => ['baz' => 'val']]], $a);
        HugeArrayHelper::set($a, 'foo.[].baz', 'val');
        $this->assertEquals(['foo' => [['baz' => 'val']]], $a);
    }

    public function test_it_can_get()
    {
        $a = [];
        $r = HugeArrayHelper::get($a, 'foo', 'val');
        $this->assertEquals('val', $r);


        $a = [];
        HugeArrayHelper::set($a, 'foo', 'val');
        $this->assertEquals(['foo' => 'val'], $a);
        $r = HugeArrayHelper::get($a, 'foo');
        $this->assertEquals('val', $r);

        $a = [];
        HugeArrayHelper::set($a, 'foo.bar', 'val');
        $this->assertEquals(['foo' => ['bar' => 'val']], $a);
        $r = HugeArrayHelper::get($a, 'foo.bar');
        $this->assertEquals('val', $r);
    }

    public function test_it_can_append()
    {
        $a = [];
        HugeArrayHelper::append($a, 'foo.[]', 1);
        $this->assertEquals(['foo' => [1]], $a);

        $a = [];
        HugeArrayHelper::append($a, 'foo.[].bar.baz', 1);
        $this->assertEquals(['foo' => [['bar' => ['baz' => 1]]]], $a);
        HugeArrayHelper::append($a, 'foo.[].bar.baz', 1);
        $this->assertEquals(['foo' => [['bar' => ['baz' => 1]], ['bar' => ['baz' => 1]]]], $a);

        // test mixed case
        $a = [];
        HugeArrayHelper::append($a, 'foo.bar.baz', 'val');
        $this->assertEquals(['foo' => ['bar' => ['baz' => 'val']]], $a);
        HugeArrayHelper::append($a, 'foo.[].baz', 'val');
        $this->assertEquals(['foo' => ['bar' => ['baz' => 'val'], ['baz' => 'val']]], $a);
    }
}