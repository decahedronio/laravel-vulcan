<?php namespace Decahedron\Tests\Vulcan;

use Decahedron\Vulcan\ElasticsearchManager;
use Elasticsearch\Client;

class ElasticManagerTest extends IntegrationTestCase
{
    public function test_it_can_connect_to_elasticsearch()
    {
        $subject = $this->manager();
        $this->assertTrue($subject->testConnection());
    }
}