<?php namespace Decahedron\Vulcan;

use Decahedron\Vulcan\Definitions\HasMappings;
use Decahedron\Vulcan\Definitions\SearchDefinition;
use Decahedron\Vulcan\Utilities\ElasticClientHelper;
use Elasticsearch\Client;
use Exception;

/**
 * This class is bound to `es` on the DI container by default.
 */
class ElasticsearchManager
{

    /**
     * @var bool
     */
    protected $enableAutoIndexer = false;

    /**
     * @var array|SearchDefinition[]
     */
    protected $definitions = [];

    /**
     * @var HasMappings
     */
    protected $commonMappings;

    /**
     * @var Client
     */
    protected $es;

    /**
     * @var string
     */
    protected $esIndex;

    /**
     * Inform the various ES services of a search definition.
     *
     * @param SearchDefinition|SearchDefinition[] $definition
     */
    public function register($definition)
    {
        if ($definition instanceof SearchDefinition) {
            $this->definitions[$definition::getSearchType()] = $definition;
        } elseif (is_array($definition)) {
            foreach ($definition as $def) {
                $this->register($def);
            }
        } else {
            throw new \RuntimeException("Invalid search definition registered: $definition");
        }
    }

    /**
     * Set a mappings class with common mappings.
     *
     * @see Tutorial on common mappings
     *
     * @param HasMappings $mappings
     */
    public function setCommonMappings(HasMappings $mappings)
    {
        $this->commonMappings = $mappings;
    }

    /**
     * @return HasMappings|null
     */
    public function getCommonMappings()
    {
        return $this->commonMappings;
    }

    public function enableAutoIndexing()
    {
        $this->enableAutoIndexer = true;
    }

    /**
     * @param ElasticClientHelper $indexer
     */
    public function boot(ElasticClientHelper $indexer)
    {
        if ($this->enableAutoIndexer) {
            foreach ($this->definitions as $definition) {
                $definition->boot($indexer);
            }
        }
    }

    /**
     * @return array|SearchDefinition[]
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @param string $type
     * @return SearchDefinition
     */
    public function getDefinitionFor(string $type): SearchDefinition
    {
        return $this->definitions[$type];
    }

    /**
     * Set ES client to use. Should be built during boot from a service provider.
     *
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->es = $client;
        return $this;
    }

    /**
     * Get ES client.
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->es;
    }

    /**
     * Set ES index to use. Should only be assigned during boot from a service provider.
     *
     * @param string $index
     * @return $this
     */
    public function setIndex(string $index)
    {
        $this->esIndex = $index;
        return $this;
    }

    /**
     * Get the ES index to use.
     *
     * @return string
     * @throws Exception
     */
    public function getIndex(): string
    {
        if (!$this->esIndex) {
            throw new Exception("No Elasticsearch index set");
        }
        return $this->esIndex;
    }

    /**
     * Get all the registered types.
     *
     * @return array|\string[]
     */
    public function getTypes()
    {
        return array_keys($this->definitions);
    }

    /**
     * @return bool
     */
    public function testConnection()
    {
        return $this->getClient()->ping([]);
    }
}