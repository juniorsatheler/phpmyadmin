<?php

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Controllers;

use PhpMyAdmin\Controllers\ExportTemplateController;
use PhpMyAdmin\Export\Template as ExportTemplate;
use PhpMyAdmin\Export\TemplateModel;
use PhpMyAdmin\Relation;
use PhpMyAdmin\Template;
use PhpMyAdmin\Tests\AbstractTestCase;
use PhpMyAdmin\Tests\Stubs\ResponseRenderer;
use PhpMyAdmin\Version;

/**
 * @covers \PhpMyAdmin\Controllers\ExportTemplateController
 */
class ExportTemplateControllerTest extends AbstractTestCase
{
    /** @var ExportTemplateController */
    private $controller;

    /** @var ResponseRenderer */
    private $response;

    /** @var Template */
    private $template;

    protected function setUp(): void
    {
        parent::setUp();
        parent::setTheme();

        global $dbi;

        $GLOBALS['server'] = 1;
        $GLOBALS['text_dir'] = 'ltr';
        $GLOBALS['PMA_PHP_SELF'] = '';

        $_SESSION['relation'][$GLOBALS['server']] = [
            'version' => Version::VERSION,
            'exporttemplateswork' => true,
            'trackingwork' => false,
            'db' => 'db',
            'export_templates' => 'table',
        ];

        $this->response = new ResponseRenderer();
        $this->template = new Template();

        $this->controller = new ExportTemplateController(
            $this->response,
            $this->template,
            new TemplateModel($dbi),
            new Relation($dbi, $this->template)
        );
    }

    public function testCreate(): void
    {
        global $cfg;

        $cfg['Server']['user'] = 'user';
        $_POST['exportType'] = 'type';
        $_POST['templateName'] = 'name';
        $_POST['templateData'] = 'data';

        $this->controller->create();

        $templates = [
            ExportTemplate::fromArray([
                'id' => 1,
                'username' => 'user1',
                'exportType' => 'type1',
                'name' => 'name1',
                'data' => 'data1',
            ]),
            ExportTemplate::fromArray([
                'id' => 2,
                'username' => 'user2',
                'exportType' => 'type2',
                'name' => 'name2',
                'data' => 'data2',
            ]),
        ];

        $options = $this->template->render('export/template_options', [
            'templates' => $templates,
            'selected_template' => null,
        ]);

        $this->assertTrue($this->response->hasSuccessState());
        $this->assertEquals(['data' => $options], $this->response->getJSONResult());
    }

    public function testDelete(): void
    {
        global $cfg;

        $cfg['Server']['user'] = 'user';
        $_POST['templateId'] = '1';

        $this->controller->delete();

        $this->assertTrue($this->response->hasSuccessState());
    }

    public function testLoad(): void
    {
        global $cfg;

        $cfg['Server']['user'] = 'user';
        $_POST['templateId'] = '1';

        $this->controller->load();

        $this->assertTrue($this->response->hasSuccessState());
        $this->assertEquals(['data' => 'data1'], $this->response->getJSONResult());
    }

    public function testUpdate(): void
    {
        global $cfg;

        $cfg['Server']['user'] = 'user';
        $_POST['templateId'] = '1';
        $_POST['templateData'] = 'data';

        $this->controller->update();

        $this->assertTrue($this->response->hasSuccessState());
    }
}
