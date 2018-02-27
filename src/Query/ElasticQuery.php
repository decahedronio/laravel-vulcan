<?php namespace Decahedron\Vulcan\Query;

use Decahedron\Vulcan\Utilities\HugeArrayHelper;

class ElasticQuery
{
    protected $query = [];

    /**
     * @param string $key
     * @param        $value
     */
    public function set(string $key, $value)
    {
        HugeArrayHelper::set($this->query, $key, $value);
    }

    /**
     * @param string $key
     * @param null   $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return HugeArrayHelper::get($this->query, $key, $default);
    }

    /**
     * Define which Elasticsearch index(es) to query.
     *
     * @param string|string[] $index
     * @return ElasticQuery
     */
    public function setIndex($index)
    {
        $this->set('index', implode(",", (array)$index));
        return $this;
    }

    /**
     * Add Elasticsearch index(es) to be queried, keeping previous defined indexes.
     *
     * @param string|string[] $index
     * @return ElasticQuery
     */
    public function addIndex($index)
    {
        if ($existing = HugeArrayHelper::get($this->query, 'index')) {
            $this->set('index', implode(",", array_unique(array_merge(explode(',', $existing), (array)$index))));
        } else {
            $this->setIndex($index);
        }
        return $this;
    }

    /**
     * Define which Elasticsearch type(s) to query.
     *
     * @param string|string[] $type
     * @return ElasticQuery
     */
    public function setType($type)
    {
        $this->set('type', implode(",", (array)$type));
        return $this;
    }

    /**
     * Add Elasticsearch type(s) to be queried, keeping previous defined types.
     *
     * @param string|string[] $type
     * @return ElasticQuery
     */
    public function addType($type)
    {
        if ($existing = HugeArrayHelper::get($this->query, 'type')) {
            $this->set('type', implode(",", array_unique(array_merge(explode(',', $existing), (array)$type))));
        } else {
            $this->setIndex($type);
        }
        return $this;
    }

    /**
     * Set pagination settings. You can also adjust these directly using `set()`.
     *
     * @param int $perPage
     * @param int $page
     * @return ElasticQuery
     */
    public function setPagination(int $perPage, int $page)
    {
        $this->set('size', $perPage);
        $this->set('from', ($page - 1) * $perPage);
        return $this;
    }

    /**
     * Convert the query builder into an ES-ready query array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->query;
    }
}