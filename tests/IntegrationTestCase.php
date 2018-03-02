<?php namespace Decahedron\Tests\Vulcan;

use Decahedron\Vulcan\ElasticsearchManager;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

abstract class IntegrationTestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    protected function manager()
    {
        $subject = new ElasticsearchManager();
        $subject->setClient(ClientBuilder::fromConfig([
            'hosts' => [getenv("ES_TEST_HOST")]
        ]));
        $subject->setIndex(getenv("ES_TEST_INDEX"));
        return $subject;
    }
}