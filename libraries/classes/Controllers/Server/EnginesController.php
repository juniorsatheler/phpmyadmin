<?php

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Server;

use PhpMyAdmin\Controllers\AbstractController;
use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Http\ServerRequest;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\StorageEngine;
use PhpMyAdmin\Template;
use PhpMyAdmin\Url;

/**
 * Handles viewing storage engine details
 */
class EnginesController extends AbstractController
{
    /** @var DatabaseInterface */
    private $dbi;

    /**
     * @param ResponseRenderer  $response
     * @param DatabaseInterface $dbi
     */
    public function __construct($response, Template $template, $dbi)
    {
        parent::__construct($response, $template);
        $this->dbi = $dbi;
    }

    public function index(): void
    {
        global $errorUrl;

        $errorUrl = Url::getFromRoute('/');

        if ($this->dbi->isSuperUser()) {
            $this->dbi->selectDb('mysql');
        }

        $this->render('server/engines/index', [
            'engines' => StorageEngine::getStorageEngines(),
        ]);
    }

    /**
     * Displays details about a given Storage Engine
     *
     * @param array $params Request params
     */
    public function show(ServerRequest $request, array $params): void
    {
        global $errorUrl;

        $errorUrl = Url::getFromRoute('/');

        if ($this->dbi->isSuperUser()) {
            $this->dbi->selectDb('mysql');
        }

        $page = $params['page'] ?? '';

        $engine = [];
        if (StorageEngine::isValid($params['engine'])) {
            $storageEngine = StorageEngine::getEngine($params['engine']);
            $engine = [
                'engine' => $params['engine'],
                'title' => $storageEngine->getTitle(),
                'help_page' => $storageEngine->getMysqlHelpPage(),
                'comment' => $storageEngine->getComment(),
                'info_pages' => $storageEngine->getInfoPages(),
                'support' => $storageEngine->getSupportInformationMessage(),
                'variables' => $storageEngine->getHtmlVariables(),
                'page' => ! empty($page) ? $storageEngine->getPage($page) : '',
            ];
        }

        $this->render('server/engines/show', [
            'engine' => $engine,
            'page' => $page,
        ]);
    }
}
