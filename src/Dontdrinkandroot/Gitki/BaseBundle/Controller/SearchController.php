<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Controller;

use Dontdrinkandroot\Gitki\BaseBundle\Repository\ElasticsearchRepository;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends BaseController
{

    public function searchAction(Request $request)
    {
        $form = $this->createFormBuilder(null, array('csrf_protection' => false))
            ->add('searchString', 'text', array('label' => 'Text'))
            ->add('search', 'submit')
            ->getForm();

        $form->handleRequest($request);

        $results = array();
        $searchString = null;
        if ($form->isValid()) {
            $searchString = $form->get('searchString')->getData();
            $results = $this->getElasticsearchRepository()->searchMarkdownDocuments($searchString);
        }

        return $this->render(
            'DdrGitkiBaseBundle:Search:search.html.twig',
            array('form' => $form->createView(), 'searchString' => $searchString, 'results' => $results)
        );
    }

    /**
     * @return \Dontdrinkandroot\Gitki\BaseBundle\Repository\ElasticsearchRepository
     */
    protected function getElasticsearchRepository()
    {
        return $this->get('ddr.gitki.repository.elasticsearch');
    }
}
