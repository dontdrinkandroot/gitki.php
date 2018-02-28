<?php

namespace App\DataFixtures;

trait ReferenceTrait
{
    /**
     * @param string $name
     *
     * @return mixed
     */
    abstract protected function getReference($name);
}
