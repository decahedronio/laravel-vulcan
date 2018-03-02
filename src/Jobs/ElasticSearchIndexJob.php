<?php namespace Decahedron\Vulcan\Jobs;

use Decahedron\Vulcan\Utilities\ElasticClientHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ElasticSearchIndexJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $id;
    /**
     * @var array
     */
    private $body;

    /**
     * @param string $type
     * @param string $id
     * @param array  $body
     */
    public function __construct(string $type, string $id, array $body)
    {
        $this->type = $type;
        $this->id = $id;
        $this->body = $body;
    }

    /**
     * @param ElasticClientHelper $indexer
     */
    public function handle(ElasticClientHelper $indexer)
    {
        $indexer->index($this->type, $this->id, $this->body);
    }
}