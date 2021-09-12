<?php

namespace ProgdogRu\ComingSoon\Middleware;

use Cake\Core\InstanceConfigTrait;
use Cake\Utility\Inflector;
use Cake\View\ViewBuilder;
use Cake\Core\Configure;

class ComingSoonMiddleware
{
    use InstanceConfigTrait;

    /**
     * Default config.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'className' => 'Cake\View\View',
        'templatePath' => 'ComingSoon',

        'configPath' => 'Settings.progdog-ru.coming-soon.enable',
        'statusCode' => 200,

        'templateFileName' => 'coming_soon',
        'layout' => 'default',

        'contentType' => 'text/html'
    ];

    /**
     * 
     */
    public function __construct($config = [])
    {
        $this->setConfig($config);
    }

    /**
     * 
     */
    public function __invoke($request, $response, $next)
    {
        $isActive = $this->isComingSoon($request);

        if ($isActive === false) {
            $response = $next($request, $response);
        } else {
            $response = $this->execute($response);
        }

        return $response;
    }

    /**
     * 
     */
    private function execute($response)
    {
        $cakeRequest = \Cake\Http\ServerRequestFactory::fromGlobals();
        $builder = new ViewBuilder();

        $className = $this->getConfig('className');
        $templateName = $this->getConfig('templateFileName');
        $templatePath = $this->getConfig('templatePath');
        $contentType = $this->getConfig('contentType');
        $statusCode = $this->getConfig('statusCode');
        $layout = $this->getConfig('layout');

        $view = $builder
            ->setClassName($className)
            ->setTemplatePath(Inflector::camelize($templatePath));


        if ($layout) {
            $view = $view->setLayout($layout);
        } else {
            $view = $view->disableAutoLayout();
        }

        $view = $view->build([], $cakeRequest);

        $bodyString = $view->render($templateName);

        $response = $response
            ->withHeader('Content-Type', $contentType)
            ->withStatus($statusCode);
        $response
            ->getBody()
            ->write($bodyString);

        return $response;
    }

    /**
     * 
     */
    private function isComingSoon($request)
    {
        $ret = $this->checkStatus();
        if ($ret === false) {
            return false;
        }

        $ret = $this->isAllow($request);
        if ($ret === true) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     * 
     */
    private function checkStatus()
    {
        $path = $this->getConfig('configPath');
        $result = Configure::read($path);
        if ($result === null) {
            $result = false;
        }

        return $result;
    }

    /**
     * TODO: проверить залогинен ли пользователь или запрос идет ли на страницу входа на сайт
     */
    private function isAllow($request)
    {
        return false;
    }
}
