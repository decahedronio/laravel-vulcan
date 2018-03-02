<?php namespace Decahedron\Vulcan\Jobs;

use Decahedron\Vulcan\Utilities\ElasticClientHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ElasticSearchDeleteIndexJob
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

    public function __construct(string $type, string $id)
    {
        $this->type = $type;
        $this->id = $id;
    }

    /**
     * @param ElasticClientHelper $indexer
     */
    public function handle(ElasticClientHelper $indexer)
    {
        $indexer->delete($this->type, $this->id);
    }
}