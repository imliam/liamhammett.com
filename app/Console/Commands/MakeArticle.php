<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;

class MakeArticle extends GeneratorCommand
{
    protected $signature = 'make:article {name}';

    protected $description = 'Make a new article';

    public function handle()
    {
        $response = parent::handle();

        if ($response !== false) {
            shell_exec("open {$this->getPath()}");
        }

        return $response;
    }

    public function getStub()
    {
        return resource_path('/stubs/article.stub');
    }

    protected function getSlug()
    {
        return Str::slug($this->argument('name'));
    }

    protected function getPath($name = null)
    {
        return base_path("content/articles/{$this->getSlug()}.md");
    }

    public function replaceClass($stub, $name)
    {
        $title = str_contains($this->argument('name'), ':') ? '"' . $this->argument('name') . '"' : $this->argument('name');
        $stub = str_replace('DummyTitle', $title, $stub);
        $stub = str_replace('DummySlug', $this->getSlug(), $stub);
        $stub = str_replace('DummyPublishedAt', Carbon::now()->toDateString(), $stub);

        return $stub;
    }
}
