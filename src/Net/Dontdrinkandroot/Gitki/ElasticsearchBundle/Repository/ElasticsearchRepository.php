<?php


namespace Net\Dontdrinkandroot\Gitki\ElasticsearchBundle\Repository;


use Elasticsearch\Client;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentDeletedEvent;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentSavedEvent;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\FilePath;
use Net\Dontdrinkandroot\Gitki\ElasticsearchBundle\Model\MarkdownSearchResult;

class ElasticsearchRepository
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

    public function deleteMarkdownDocumentIndex()
    {
        //TODO: This doesn't work with the current elasticsearch api version
//        $params = array(
//            'index' => $this->index,
//            'type' => 'markdown_document',
//        );
//        $params['body']['query']['bool']['must']['match_all'] = '';
//
//        return $this->client->deleteByQuery($params);
    }

    public function indexMarkdownDocument(FilePAth $path, ParsedMarkdownDocument $parsedMarkdownDocument)
    {
        $params = array(
            'id' => $path->toAbsoluteUrlString(),
            'index' => $this->index,
            'type' => 'markdown_document',
            'body' => array(
                'title' => $parsedMarkdownDocument->getTitle(),
                'content' => $parsedMarkdownDocument->getSource()
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
            'type' => 'markdown_document',
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
            $searchResult->setTitle($hit['_source']['title']);
            $searchResult->setScore($hit['_score']);
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
            'type' => 'markdown_document',
        );

        return $this->client->delete($params);
    }

} 