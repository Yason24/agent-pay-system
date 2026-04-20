<?php

namespace Framework\Core\View;

class ViewFactory
{
    protected string $viewsPath;
    protected BladeCompiler $compiler;

    public function __construct(string $viewsPath, string $cachePath)
    {
        $this->viewsPath = $viewsPath;

        if (!is_dir($cachePath) && !mkdir($cachePath, 0777, true) && !is_dir($cachePath)) {
            throw new \RuntimeException("Unable to create cache directory: {$cachePath}");
        }

        $this->compiler = new BladeCompiler($cachePath);
    }

    public function make(string $view, array $data = [])
    {
        $path = $this->viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($path)) {
            throw new \Exception("View [$view] not found");
        }

        $compiled = $this->compiler->compile($path);
        $viewInstance = new View();
        $content = $this->renderCompiled($compiled, $viewInstance, $data);

        if ($layout = $viewInstance->getLayout()) {
            $layoutPath = $this->viewsPath . '/' . str_replace('.', '/', $layout) . '.php';

            if (!file_exists($layoutPath)) {
                throw new \Exception("Layout [$layout] not found");
            }

            $compiledLayout = $this->compiler->compile($layoutPath);

            return $this->renderCompiled(
                $compiledLayout,
                $viewInstance,
                array_merge($data, ['content' => $content])
            );
        }

        return $content;
    }

    protected function renderCompiled(string $compiledPath, View $viewInstance, array $data = []): string
    {
        ob_start();

        (function () use ($compiledPath, $data) {
            extract($data, EXTR_SKIP);
            include $compiledPath;
        })->call($viewInstance);

        return ob_get_clean();
    }
}