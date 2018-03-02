<?php namespace Decahedron\Vulcan\Jobs;

use Decahedron\Vulcan\Exceptions\FailedMassIndexException;
use Decahedron\Vulcan\Utilities\ElasticClientHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class ElasticSearchMassIndexJob
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    private $items;

    /**
     * @var string
     */
    private $targetIndex;

    /**
     * @param array  $items Array of [string $type, string $id, array $body].
     * @param string $targetIndex
     */
    public function __construct(array $items, string $targetIndex)
    {
        $this->items = $items;
        $this->targetIndex = $targetIndex;
    }

    /**
     * @param ElasticClientHelper $indexer
     * @throws FailedMassIndexException
     */
    public function handle(ElasticClientHelper $indexer)
    {
        try {
            $indexer->massIndex($this->items, $this->targetIndex);
        } catch (FailedMassIndexException $e) {
            throw $e;
        }
    }
}