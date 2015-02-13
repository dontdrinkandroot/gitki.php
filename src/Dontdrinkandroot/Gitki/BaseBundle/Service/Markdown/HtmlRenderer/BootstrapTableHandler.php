<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Service\Markdown\HtmlRenderer;

use Net\Dontdrinkandroot\ObjectiveMarkdown\Model\Element\Block\Table\Table;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Renderer\Html\Handler\TableHandler;

class BootstrapTableHandler extends TableHandler
{

    /**
     * @inheritdoc
     */
    protected function createTableAttributes(Table $table)
    {
        return [
            'class' => 'table'
        ];
    }
}
