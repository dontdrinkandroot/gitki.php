<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Twig;

class GitkiExtension extends \Twig_Extension
{


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'gitki_extension';
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('dirTitle', array($this, 'titleFilter')),
        );
    }

    public function titleFilter($title)
    {
        $words = explode('_', $title);
        $transformedTitle = '';
        for ($i = 0; $i < count($words) - 1; $i++) {
            $transformedTitle .= ucfirst($words[$i]) . ' ';
        }
        $transformedTitle .= ucfirst($words[count($words) - 1]);

        return $transformedTitle;
    }
}
