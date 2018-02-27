<?php namespace Decahedron\Tests\Vulcan\Query;

use Decahedron\Tests\Vulcan\TestCase;
use Decahedron\Vulcan\Query\ElasticQuery;

class ElasticQueryTest extends TestCase
{


    public function test_it_gets_a_blank_query_by_default()
    {
        $q = new ElasticQuery();
        $this->assertEquals([], $q->toArray());
    }

    public function test_it_allows_raw_select()
    {
        $q = new ElasticQuery();
        $q->set("index", "foo");
        $this->assertEquals(['index' => 'foo'], $q->toArray());

        $q = new ElasticQuery();
        $q->set("body.foo.bar", 'test');
        $this->assertEquals(['body' => ['foo' => ['bar' => 'test']]], $q->toArray());
    }

    public function test_it_accepts_an_index_or_indexes()
    {
        $q = new ElasticQuery();
        $q->setIndex("foo");
        $this->assertEquals(['index' => 'foo'], $q->toArray());

        $q = new ElasticQuery();
        $q->setIndex(["foo", "bar"]);
        $this->assertEquals(['index' => 'foo,bar'], $q->toArray());

        // multiple sets will override each other
        $q = new ElasticQuery();
        $q->setIndex("foo");
        $q->setIndex("bar");
        $this->assertEquals(['index' => 'bar'], $q->toArray());

        // use add to append
        $q = new ElasticQuery();
        $q->setIndex("foo");
        $q->addIndex("bar");
        $this->assertEquals(['index' => 'foo,bar'], $q->toArray());
    }

    public function test_it_accepts_a_type_or_types()
    {
        $q = new ElasticQuery();
        $q->setIndex("foo");
        $q->setType("users");
        $this->assertEquals(['index' => 'foo', 'type' => 'users'], $q->toArray());

        // multiple setType()s will override
        $q = new ElasticQuery();
        $q->setIndex("foo");
        $q->setType("users");
        $q->setType("posts");
        $this->assertEquals(['index' => 'foo', 'type' => 'posts'], $q->toArray());

        // use add to append
        $q = new ElasticQuery();
        $q->setIndex("foo");
        $q->setType("users");
        $q->addType("posts");
        $this->assertEquals(['index' => 'foo', 'type' => 'users,posts'], $q->toArray());
        $q->addType(["posts", "videos"]);
        $this->assertEquals(['index' => 'foo', 'type' => 'users,posts,videos'], $q->toArray());

        // use add to append
        $q = new ElasticQuery();
        $q->setIndex("foo");
        $q->setType("users");
        $q->addType(["posts", "uploads"]);
        $this->assertEquals(['index' => 'foo', 'type' => 'users,posts,uploads'], $q->toArray());
    }

    public function test_it_accepts_pagination_settings()
    {
        $q = new ElasticQuery();
        $q->setPagination(15, 1);
        $this->assertEquals(['size' => '15', 'from' => 0], $q->toArray());

        $q = new ElasticQuery();
        $q->setPagination(15, 2);
        $this->assertEquals(['size' => '15', 'from' => 15], $q->toArray());

        $q = new ElasticQuery();
        $q->setPagination(15, 3);
        $this->assertEquals(['size' => '15', 'from' => 30], $q->toArray());
    }
}