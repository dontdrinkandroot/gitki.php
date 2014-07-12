<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Controller;


use GitWrapper\GitException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockedException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WikiController extends BaseController
{

    public function listDirectoryAction($path = '')
    {
        $locator = new Path($path);
        $directoryListing = $this->getWikiService()->listDirectory($locator);
        return $this->render(
            'DdrGitkiBaseBundle:Wiki:directoryListing.html.twig',
            array(
                'directoryListing' => $directoryListing
            )
        );
    }

    public function pageAction($path)
    {
        $locator = new Path($path);

        if (!$this->getWikiService()->pageExists($locator)) {

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

        $content = $this->getWikiService()->getContent($locator);
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
                'path' => $path,
                'locator' => $locator
            )
        );
    }

    public function pageEditAction(Request $request, $path)
    {
        $locator = new Path($path);
        $user = $this->getUser();

        try {
            $this->getWikiService()->createLock($user, $locator);
        } catch (PageLockedException $e) {
            throw new ConflictHttpException($e->getMessage());
        }
        $content = $this->getWikiService()->getContent($locator);

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
                $this->getWikiService()->savePage($user, $locator, $content, $commitMessage);
                $this->getWikiService()->removeLock($user, $locator);
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

    public function deletePageAction($path)
    {
        $locator = new Path($path);
        $directoryIndexPath = $locator->getParentPath()->toString();
        $user = $this->getUser();

        $this->getWikiService()->deletePage($user, $locator);

        return $this->redirect(
            $this->generateUrl(
                'ddr_gitki_wiki_directory',
                array('path' => $directoryIndexPath)
            )
        );
    }

}