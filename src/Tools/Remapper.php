<?php namespace Decahedron\Vulcan\Tools;

use Decahedron\Vulcan\Definitions\HasStandaloneMappings;
use Decahedron\Vulcan\ElasticsearchManager;
use Decahedron\Vulcan\Support\EmitsEvents;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Illuminate\Support\Collection;

class Remapper
{

    use EmitsEvents;

    /**
     * @var ElasticsearchManager
     */
    private $manager;

    /**
     * @var array|HasStandaloneMappings[]
     */
    protected $extraMappings = [];

    public function __construct(ElasticsearchManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param HasStandaloneMappings|HasStandaloneMappings[]|Collection $mappings
     * @throws \Exception
     */
    public function registerExtraMapping($mappings)
    {
        if ($mappings instanceof HasStandaloneMappings) {
            $this->extraMappings[] = $mappings;
        } else if (is_array($mappings)) {
            $this->extraMappings = array_merge($this->extraMappings, $mappings);
        } else if ($mappings instanceof Collection) {
            $this->extraMappings = array_merge($this->extraMappings, $mappings->toArray());
        } else {
            throw new \Exception("Invalid mapping registered in SearchServiceProvider");
        }
    }

    /**
     * @param string $index
     * @return array
     */
    public function remap(string $index)
    {
        $report = [];

        // collect global mappings
        if ($this->manager->getCommonMappings()) {
            $this->fire('common.adding');
            $globalMapping = array_map(function ($mapping) {
                return $this->prepareDatatype($mapping);
            }, $this->manager->getCommonMappings()->getFieldMapping());
            $this->fire('common.added', $globalMapping);

            // add common mappings to report
            $report[] = [
                'definition' => get_class($this->manager->getCommonMappings()),
                'type'       => 'n/a',
                'count'      => count($globalMapping),
            ];
        } else {
            $globalMapping = [];
        }

        // add extra mappings
        foreach ($this->extraMappings as $mapping) {
            $this->fire('extra.adding', $mapping);
            $prepared = array_map(function ($mapping) {
                return $this->prepareDatatype($mapping);
            }, $mapping->getFieldMapping());

            $this->execute($index, $mapping->getType(), $prepared);
            $this->fire('extra.added', $mapping);

            $report[] = [
                'definition' => get_class($mapping),
                'type' => $mapping->getType(),
                'count' => count($mapping->getFieldMapping()),
            ];
        }

        try {
            // process individual mappings
            foreach ($this->manager->getDefinitions() as $definition) {
                $this->fire('definition.adding', $definition);
                // prepare any shorthand definitions
                $prepared = array_map(function ($mapping) {
                    return $this->prepareDatatype($mapping);
                }, $definition->getFieldMapping());

                // execute mappings along with global
                $result = $this->execute($index, $definition->getSearchType(), array_merge($globalMapping, $prepared));
                $this->fire('definition.added', $definition);

                // append to report
                $report[] = [
                    'definition' => get_class($definition),
                    'type'       => $definition->getSearchType(),
                    'count'      => count($definition->getFieldMapping()),
                ];

            }
        } catch (BadRequest400Exception $e) {
            $error = json_decode($e->getMessage())->error->reason;
            $this->fire('error', "Mapping execution error: {$error} ");
        }

        return $report;
    }

    /**
     * If the given datatype is just a string, we assume that's the type, with no other properties. Otherwise we pass
     * through the original structure.
     *
     * @param string|array $input
     * @return array|string
     */
    protected function prepareDatatype($input)
    {
        if (is_string($input)) {
            return ['type' => $input];
        }
        return $input;
    }


    protected function execute(string $index, string $type, array $mapping, bool $updateAllTypes = false)
    {
        $params = [
            'index' => $index,
            'type'  => $type,
            'body'  => [
                $type => [
                    'properties' => $mapping
                ]
            ]
        ];

        if ($updateAllTypes) {
            $params['update_all_types'] = true;
        }

        return $this->manager->getClient()->indices()->putMapping($params);
    }
}