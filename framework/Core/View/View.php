<?php

namespace Framework\Core\View;

class View
{
    protected array $sections = [];
    protected array $sectionStack = [];
    protected ?string $layout = null;

    public function extend(string $layout)
    {
        $this->layout = $layout;
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function startSection(string $name)
    {
        $this->sectionStack[] = $name;
        ob_start();
    }

    public function endSection()
    {
        $name = array_pop($this->sectionStack);
        $this->sections[$name] = ob_get_clean();
    }

    public function yieldSection(string $name)
    {
        return $this->sections[$name] ?? '';
    }
}