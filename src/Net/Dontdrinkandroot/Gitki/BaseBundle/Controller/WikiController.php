<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Controller;


use GitWrapper\GitException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WikiController extends BaseController
{

    public function indexAction()
    {
        return $this->redirect($this->generateUrl('ddr_gitki_wiki_page', array('path' => 'index')));
    }

    public function pageAction($path)
    {
        if (!$this->getWikiService()->pageExists($path)) {

            if (null === $this->getUser()) {
                throw new NotFoundHttpException('This page does not exist');
            }

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_wiki_page_edit',
                    array('path' => $path)
                )
            );
        }

        $content = $this->getWikiService()->getContent($path);
        $content = $this->getMarkdownParser()->transformMarkdown($content);

        $heading = null;
        $body = null;
        if (preg_match("#<h1.*?>(.*?)</h1>#i", $content, $matches)) {
            $heading = $matches[1];
        }
        $body = preg_replace("#<h1.*</h1>#i", "", $content);

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:page.html.twig',
            array(
                'heading' => $heading,
                'body' => $body,
                'path' => $path
            )
        );
    }

    public function pageEditAction(Request $request, $path)
    {
        $user = $this->getUser();

        try {
            $this->getWikiService()->createLock($user, $path);
        } catch (PageLockedException $e) {
            throw new ConflictHttpException($e->getMessage());
        }
        $content = $this->getWikiService()->getContent($path);

        $form = $this->createFormBuilder()
            ->add('content', 'textarea')
            ->add('commitMessage', 'text', array('label' => 'Commit Message', 'required' => true))
            ->add('save', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $content = $form->get('content')->getData();
            $commitMessage = $form->get('commitMessage')->getData();
            try {
                $this->getWikiService()->savePage($user, $path, $content, $commitMessage);
                $this->getWikiService()->removeLock($user, $path);
                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_wiki_page',
                        array('path' => $path)
                    )
                );
            } catch (GitException $e) {
                throw $e;
            }
        } else {
            if (!$form->isSubmitted()) {
                $form->setData(
                    array(
                        'content' => $content,
                        'commitMessage' => 'Editing ' . $path
                    )
                );
            }
        }


        return $this->render('DdrGitkiBaseBundle:Wiki:pageedit.html.twig', array('form' => $form->createView()));
    }

} 