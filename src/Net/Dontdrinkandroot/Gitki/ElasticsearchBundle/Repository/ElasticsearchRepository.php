<?php


namespace Net\Dontdrinkandroot\Gitki\ElasticsearchBundle\Repository;


use Elasticsearch\Client;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentDeletedEvent;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentSavedEvent;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Net\Dontdrinkandroot\Gitki\ElasticsearchBundle\Model\MarkdownSearchResult;
use Net\Dontdrinkandroot\Utils\Path\FilePath;

class ElasticsearchRepository
{

    const MARKDOWN_DOCUMENT_TYPE = 'markdown_document';

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

    public function deleteMarkdownDocumentIndex()
    {
        /* TODO: Delete without id is not supported by current elasticsearch PHP API
        $params = array(
            'index' => $this->index,
            'type' => self::MARKDOWN_DOCUMENT_TYPE,
        );

        return $this->client->delete($params);*/

        $params = array(
            'index' => $this->index,
            'type' => self::MARKDOWN_DOCUMENT_TYPE,
            'fields' => array('_id')
        );

        $params['body']['query']['match_all'] = array();
        $params['body']['size'] = 10000;

        $result = $this->client->search($params);
        foreach ($result['hits']['hits'] as $hit) {
            $params = array(
                'id' => $hit['_id'],
                'index' => $this->index,
                'type' => self::MARKDOWN_DOCUMENT_TYPE,
            );

            $this->client->delete($params);
        }
    }

    public function indexMarkdownDocument(FilePAth $path, ParsedMarkdownDocument $parsedMarkdownDocument)
    {
        $params = array(
            'id' => $path->toAbsoluteUrlString(),
            'index' => $this->index,
            'type' => self::MARKDOWN_DOCUMENT_TYPE,
            'body' => array(
                'title' => $parsedMarkdownDocument->getTitle(),
                'content' => $parsedMarkdownDocument->getSource(),
                'linked_paths' => $parsedMarkdownDocument->getLinkedPaths()
            )
        );

        return $this->client->index($params);
    }

    /**
     * @param $searchString
     * @return MarkdownSearchResult[]
     */
    public function searchMarkdownDocuments($searchString)
    {
        $params = array(
            'index' => $this->index,
            'type' => self::MARKDOWN_DOCUMENT_TYPE,
            'fields' => array('title')
        );

        $searchStringParts = explode(' ', $searchString);
        foreach ($searchStringParts as $searchStringPart) {
            $params['body']['query']['bool']['should'][]['wildcard']['content'] = $searchStringPart . '*';
        }

        $result = $this->client->search($params);
        $numHits = $result['hits']['total'];
        if ($numHits == 0) {
            return array();
        }

        $searchResults = array();
        foreach ($result['hits']['hits'] as $hit) {
            $searchResult = new MarkdownSearchResult();
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

    public function onMarkdownDocumentSaved(MarkdownDocumentSavedEvent $event)
    {
        $this->indexMarkdownDocument($event->getPath(), $event->getDocument());
    }

    public function onMarkdownDocumentDeleted(MarkdownDocumentDeletedEvent $event)
    {
        $params = array(
            'id' => $event->getPath()->toAbsoluteUrlString(),
            'index' => $this->index,
            'type' => self::MARKDOWN_DOCUMENT_TYPE,
        );

        return $this->client->delete($params);
    }

} 