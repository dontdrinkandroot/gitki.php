<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Controller;


use Github\Client;
use Symfony\Component\HttpFoundation\Request;

class GithubController extends BaseController
{

    const ORGANIZATION = 'dontdrinkandroot';
    const PROJECT = 'gitki.php';

    public function issuesAction()
    {
        $client = new Client();
        /* @var \Github\Api\Issue $issue */
        $issue = $client->api('issue');
        $issues = $issue->all(self::ORGANIZATION, self::PROJECT);

        return $this->render('DdrGitkiBaseBundle:Github:issues.html.twig', array('issues' => $issues));
    }

    public function createIssueAction(Request $request)
    {
        $this->assertRole('ROLE_USER');

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

                $client = new Client();
                $accessToken = $this->getUser()->getAccessToken();
                $client->authenticate($accessToken, Client::AUTH_HTTP_TOKEN);
                /* @var \Github\Api\Issue $issue */
                $issue = $client->api('issue');
                $params = array(
                    'title' => $title,
                    'body' => $body
                );
                $issue->create(self::ORGANIZATION, self::PROJECT, $params);

                $this->redirect($this->generateUrl('ddr_gitki_github_issues'));
            }
        }

        return $this->render('DdrGitkiBaseBundle:Github:issue_create.html.twig', array('form' => $form->createView()));
    }


} 