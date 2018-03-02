<?php namespace Decahedron\Vulcan\Utilities;

use Decahedron\Vulcan\Exceptions\FailedMassIndexException;

class ElasticClientHelper
{

    /**
     * @var ElasticsearchManager
     */
    private $manager;

    /**
     * @param ElasticsearchManager $manager
     */
    public function __construct(ElasticsearchManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $type
     * @param string $id
     * @param array  $body
     * @throws \Exception
     */
    public function index(string $type, string $id, array $body)
    {
        $this->manager->getClient()->index([
            'index' => $this->manager->getIndex(),
            'type'  => $type,
            'id'    => $id,
            'body'  => $body,
        ]);
    }

    /**
     * @param string $type
     * @param string $id
     * @param array  $body
     * @throws \Exception
     */
    public function update(string $type, string $id, array $body)
    {
        $this->manager->getClient()->update([
            'index' => $this->manager->getIndex(),
            'type'  => $type,
            'id'    => $id,
            'body'  => $body,
        ]);
    }

    /**
     * @param array  $items Array of [string $type, string $id, array $body].
     * @param string $targetIndex
     * @return array
     * @throws FailedMassIndexException
     */
    public function massIndex(array $items, string $targetIndex = null)
    {
        $body = [];
        foreach ($items as $item) {
            $body[] = [
                'index' => [
                    '_index' => $targetIndex ?? $this->manager->getIndex(),
                    '_type' => $item[0],
                    '_id' => $item[1]
                ]
            ];
            $body[] = $item[2];
        }

        $result = $this->manager->getClient()->bulk([
            'body' => $body,
        ]);

        if ($result['errors'] === true) {
            throw new FailedMassIndexException('Error in mass index.', $result);
        }
        return $result;
    }

    /**
     * @param string $type
     * @param string $id
     */
    public function delete(string $type, string $id)
    {
        $this->manager->getClient()->delete([
            'index' => $this->manager->getIndex(),
            'type'  => $type,
            'id'    => $id,
        ]);
    }
}