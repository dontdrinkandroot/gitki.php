<?php


namespace Net\Dontdrinkandroot\Gitki\GitHubIssuesBundle\Controller;


use Github\Api\Issue;
use Github\Client;
use Github\HttpClient\CachedHttpClient;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class IssuesController extends BaseController
{

    const ORGANIZATION = 'dontdrinkandroot';
    const PROJECT = 'gitki.php';

    public function listAction()
    {
        $issues = $this->getIssueApi()->all(self::ORGANIZATION, self::PROJECT);

        return $this->render('DdrGitkiGitHubIssuesBundle:Issues:list.html.twig', array('issues' => $issues));
    }

    public function createAction(Request $request)
    {
        $this->assertRole('ROLE_GITHUB_USER');

        $form = $this->createFormBuilder()
            ->add('title', 'text', array('label' => 'Title', 'required' => true))
            ->add('body', 'textarea', array('label' => 'Body', 'required' => false))
            ->add('Submit', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                $title = $form->get('title')->getData();
                $body = $form->get('body')->getData();
                if (null === $body) {
                    $body = "";
                }

                $accessToken = $this->getUser()->getAccessToken();
                $this->getIssueApi($accessToken)->create(
                    self::ORGANIZATION,
                    self::PROJECT,
                    array(
                        'title' => $title,
                        'body' => $body
                    )
                );

                return $this->redirect($this->generateUrl('ddr_gitki_issues_list'));
            }
        }

        return $this->render(
            'DdrGitkiGitHubIssuesBundle:Issues:create.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @param string|null $accessToken
     * @return Issue
     */
    protected function getIssueApi($accessToken = null)
    {
        $client = new Client(
            new CachedHttpClient(array('cache_dir' => '/tmp/github-api-cache'))
        );
        if (null !== $accessToken) {
            $client->authenticate($accessToken, "", Client::AUTH_HTTP_PASSWORD);
        }
        $issueApi = $client->api('issue');

        return $issueApi;
    }


} 