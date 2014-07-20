<?php


namespace Net\Dontdrinkandroot\Gitki\ElasticSearchBundle\Repository;


use Elasticsearch\Client;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentSavedEvent;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\FilePath;

class ElasticSearchRepository
{

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

        $params = array();
        $params['hosts'] = array($host . ':' . $port);
        $this->client = new Client($params);
    }

    public function onMarkdownDocumentSaved(MarkdownDocumentSavedEvent $event)
    {
        $this->indexMarkdownDocument($event->getPath(), $event->getContent());
    }

    public function indexMarkdownDocument(FilePath $path, $content)
    {
        $params = array(
            'id' => $path->toString(),
            'index' => $this->index,
            'type' => 'markdown_document',
            'body' => array(
                'content' => $content
            )
        );

        return $this->client->index($params);
    }

    /**
     * @param $searchString
     * @return FilePath[]
     */
    public function searchMarkdownDocuments($searchString)
    {
        $params = array(
            'index' => $this->index,
            'type' => 'markdown_document',
        );
        $params['body']['query']['match']['content'] = $searchString;

        $result = $this->client->search($params);
        $numHits = $result['hits']['total'];
        if ($numHits == 0) {
            return array();
        }

        $paths = array();
        foreach ($result['hits']['hits'] as $hit) {
            $paths[] = new FilePath($hit['_id']);
        }

        return $paths;
    }

} 