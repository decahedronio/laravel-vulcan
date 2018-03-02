<?php namespace Decahedron\Vulcan\Definitions;

use Decahedron\Vulcan\Utilities\ElasticClientHelper;
use Illuminate\Database\Eloquent\Model;

abstract class SearchDefinition
{
    /**
     * Inject an instance of the model in search definitions!
     *
     * @var Model
     */
    protected $model;

    /**
     * Replace this with your model type in search definitions!
     *
     * @var string
     */
    protected static $type = 'untyped';

    /**
     * @param ElasticClientHelper $indexer
     */
    public function boot(ElasticClientHelper $indexer)
    {
        $this->model->created(function ($model) use ($indexer) {
            if ($this->shouldIndex($model)) {
                dispatch(new ElasticSearchIndexJob(static::$type,
                    $this->getSearchId($model),
                    $this->toSearchableArray($model)));
            }
        });

        $this->model->saved(function ($model) use ($indexer) {
            if ($this->shouldIndex($model)) {
                ElasticSearchIndexJob::dispatch(static::$type,
                    $this->getSearchId($model),
                    $this->toSearchableArray($model));
            } else {
                try {
                    ElasticSearchDeleteIndexJob::dispatch(static::$type, $this->getSearchId($model));
                } catch (ElasticsearchException $e) {
                }
            }
        });

        $this->model->deleted(function ($model) use ($indexer) {
            try {
                ElasticSearchDeleteIndexJob::dispatch(static::$type, $this->getSearchId($model));
            } catch (ElasticsearchException $e) {
            }
        });
    }

    /***
     * Extend this method if you have special logic regarding when a model should be indexed.
     *
     * This is called upon create and save. If it returns true, we dispatch an index job; if it returns false,
     * we delete any existing index for this model.
     *
     * @param $model
     * @return bool
     */
    public function shouldIndex($model)
    {
        return true;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Define the ES type for this model. Don't override this method - change the $type property instead.
     *
     * @return string
     */
    public static function getSearchType(): string
    {
        return static::$type;
    }

    /**
     * If you use UUIDs or another proper way to refer to individual models, change this here. Search IDs become
     * the model's ID in ES.
     *
     * @param Model $model
     * @return string
     */
    public function getSearchId($model): string
    {
        return $model->getKey();
    }

    /**
     * Y
     *
     * Be aware that these will be serialized when passing into a job, so any data here should be safe-to-serialize,
     * and will not be further processed until passed into Elasticsearch. Be extra aware of Carbon objects!
     *
     * @see Tutorial on Common Fields
     *
     * @param Model $model
     * @return array
     */
    abstract public function toSearchableArray($model): array;

    /**
     * Return a list of fields and their types. This is for building ES fields mappings.
     *
     * @return array
     */
    public static function getFieldMapping(): array
    {
        return [];
    }
}