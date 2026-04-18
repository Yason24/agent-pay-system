<?php

namespace Framework\Core\View;

class BladeCompiler
{
    protected string $cachePath;

    public function __construct(string $cachePath)
    {
        $this->cachePath = $cachePath;
    }

    public function compile(string $path): string
    {
        $content = file_get_contents($path);

        $content = $this->compileEchos($content);
        $content = $this->compileExtends($content);
        $content = $this->compileSections($content);
        $content = $this->compileYields($content);

        $compiled = $this->cachePath.'/'.md5($path).'.php';

        file_put_contents($compiled, $content);

        return $compiled;
    }

    protected function compileEchos($content)
    {
        return preg_replace(
            '/{{\s*(.+?)\s*}}/',
            '<?= htmlspecialchars($1) ?>',
            $content
        );
    }

    protected function compileExtends($content)
    {
        return preg_replace(
            "/@extends\(['\"](.+?)['\"]\)/",
            "<?php \$this->extend('$1'); ?>",
            $content
        );
    }

    protected function compileSections($content)
    {
        $content = preg_replace(
            "/@section\(['\"](.+?)['\"]\)/",
            "<?php \$this->startSection('$1'); ?>",
            $content
        );

        return preg_replace(
            "/@endsection/",
            "<?php \$this->endSection(); ?>",
            $content
        );
    }

    protected function compileYields($content)
    {
        return preg_replace(
            "/@yield\(['\"](.+?)['\"]\)/",
            "<?= \$this->yieldSection('$1'); ?>",
            $content
        );
    }
}