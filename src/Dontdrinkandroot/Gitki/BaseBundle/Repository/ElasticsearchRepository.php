<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Repository;

use Dontdrinkandroot\Gitki\BaseBundle\Analyzer\AnalyzerInterface;
use Dontdrinkandroot\Gitki\BaseBundle\Event\FileChangedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\FileDeletedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\FileMovedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentDeletedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentSavedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Model\SearchResult;
use Dontdrinkandroot\Path\FilePath;
use Elasticsearch\Client;

class ElasticsearchRepository implements ElasticsearchRepositoryInterface
{

    /**
     * @var AnalyzerInterface[];
     */
    protected $analyzers = [];

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var Client
     */
    private $client;

    public function __construct($host, $port, $index)
    {
        $this->host = $host;
        $this->port = $port;
        $this->index = strtolower($index);

        $params = [];
        $params['hosts'] = [$host . ':' . $port];
        $this->client = new Client($params);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        /* TODO: Delete without id is not supported by current elasticsearch PHP API
        $params = array(
            'index' => $this->index,
            'type' => self::MARKDOWN_DOCUMENT_TYPE,
        );

        return $this->client->delete($params);*/

        $params = [
            'index' => $this->index,
            'fields' => array('_id')
        ];

        $params['body']['query']['match_all'] = [];
        $params['body']['size'] = 10000;

        $result = $this->client->search($params);

        foreach ($result['hits']['hits'] as $hit) {
            $params = array(
                'id'   => $hit['_id'],
                'index' => $this->index,
                'type' => $hit['_type']
            );

            $this->client->delete($params);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function search($searchString)
    {
        $params = [
            'index'  => $this->index,
            'fields' => ['title']
        ];

        $searchStringParts = explode(' ', $searchString);
        foreach ($searchStringParts as $searchStringPart) {
            $params['body']['query']['bool']['should'][]['wildcard']['content'] = $searchStringPart . '*';
        }

        $result = $this->client->search($params);
        $numHits = $result['hits']['total'];
        if ($numHits == 0) {
            return [];
        }

        $searchResults = [];
        foreach ($result['hits']['hits'] as $hit) {
            $searchResult = new SearchResult();
            $searchResult->setPath(FilePath::parse($hit['_id']));
            $searchResult->setScore($hit['_score']);
            if (isset($hit['fields'])) {
                if (isset($hit['fields']['title'][0])) {
                    $searchResult->setTitle($hit['fields']['title'][0]);
                }
            }
            $searchResults[] = $searchResult;
        }

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function onFileChanged(FileChangedEvent $event)
    {
        $this->addFile($event->getFile());
    }

    /**
     * {@inheritdoc}
     */
    public function onFileDeleted(FileDeletedEvent $event)
    {
        $this->deleteFile($event->getFile());
    }

    /**
     * {@inheritdoc}
     */
    public function onFileMoved(FileMovedEvent $event)
    {
        $this->deleteFile($event->getPreviousFile());
        $this->addFile($event->getFile());
    }

    /**
     * {@inheritdoc}
     */
    public function addFile(FilePath $path)
    {
        if (!isset($this->analyzers[$path->getExtension()])) {
            return null;
        }

        $analyzer = $this->analyzers[$path->getExtension()];
        $result = $analyzer->analyze($path);
        $params = [
            'id'   => $path->toAbsoluteString(),
            'index' => $this->index,
            'type' => $path->getExtension(),
            'body' => [
                'title'        => $result->getTitle(),
                'content'      => $result->getContent(),
                'linked_paths' => $result->getLinkedPaths()
            ]
        ];

        return $this->client->index($params);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(FilePath $path)
    {
        $params = [
            'id'    => $path->toAbsoluteString(),
            'index' => $this->index,
            'type'  => $path->getExtension()
        ];

        return $this->client->delete($params);
    }

    public function getTitle(FilePath $path)
    {
        $params = array(
            'index'           => $this->index,
            'id'              => $path->toAbsoluteString(),
            '_source_include' => array('title')
        );
        $result = $this->client->get($params);
        if (null === $result) {
            return null;
        }

        return $result['_source']['title'];
    }

    public function registerAnalyzer(AnalyzerInterface $analyzer)
    {
        foreach ($analyzer->getSupportedExtensions() as $extension) {
            $this->analyzers[$extension] = $analyzer;
        }
    }
}
