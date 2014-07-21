<?php


namespace Net\Dontdrinkandroot\Gitki\ElasticSearchBundle\Controller;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Controller\BaseController;
use Net\Dontdrinkandroot\Gitki\ElasticSearchBundle\Repository\ElasticSearchRepository;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BaseController
{

    public function searchAction(Request $request)
    {
        $form = $this->createFormBuilder(null, array('csrf_protection' => false))
            ->add('searchString', 'text', array('label' => 'Text'))
            ->add('search', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $searchString = $form->get('searchString')->getData();
            $results = $this->getElasticSearchRepository()->searchMarkdownDocuments($searchString);

            return $this->render(
                'DdrGitkiElasticSearchBundle:Default:search.html.twig',
                array('searchString' => $searchString, 'results' => $results)
            );
        }
    }

    /**
     * @return ElasticSearchRepository
     */
    protected function getElasticSearchRepository()
    {
        return $this->get('ddr.gitki.repository.elasticsearch');
    }

} 