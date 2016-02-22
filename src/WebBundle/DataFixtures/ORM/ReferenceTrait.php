<?php


namespace Dontdrinkandroot\Gitki\WebBundle\DataFixtures\ORM;

trait ReferenceTrait
{

    /**
     * @param string $name
     *
     * @return mixed
     */
    abstract protected function getReference($name);
}