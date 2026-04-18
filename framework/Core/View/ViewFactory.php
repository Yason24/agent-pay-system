<?php

namespace Framework\Core\View;

class ViewFactory
{
    protected string $viewsPath;
    protected BladeCompiler $compiler;

    public function __construct(string $viewsPath)
    {
        $this->viewsPath = $viewsPath;

        $this->compiler = new BladeCompiler(
            ROOT.'/storage/cache/views'
        );
    }

    public function make(string $view, array $data = [])
    {
        $path = $this->viewsPath.'/'.str_replace('.', '/', $view).'.php';

        if (!file_exists($path)) {
            throw new \Exception("View [$view] not found");
        }

        $compiled = $this->compiler->compile($path);

        $viewInstance = new View();

        extract($data);

        ob_start();
        include $compiled;
        $content = ob_get_clean();

        if ($viewInstance->getLayout()) {

            $layoutPath =
                $this->viewsPath.'/'.str_replace(
                    '.',
                    '/',
                    $viewInstance->getLayout()
                ).'.php';

            $compiledLayout = $this->compiler->compile($layoutPath);

            ob_start();
            include $compiledLayout;

            return ob_get_clean();
        }

        return $content;
    }
}