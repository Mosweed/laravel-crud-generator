<?php

namespace Mosweed\CrudGenerator\Generators;

use Illuminate\Support\Facades\File;

class RouteGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $isApi = $this->config['api_only'] ?? config('crud-generator.api_only');
        
        if ($isApi) {
            return $this->appendApiRoutes();
        }

        return $this->appendWebRoutes();
    }

    protected function appendWebRoutes(): bool
    {
        $routeFile = config('crud-generator.paths.route') . '/web.php';
        $controllerName = $this->getControllerName();
        $routePrefix = $this->getRoutePrefix();
        $routeName = $routePrefix;

        $routes = <<<PHP


// {$this->modelName} Routes
use App\\Http\\Controllers\\{$controllerName};

Route::middleware({$this->formatMiddleware('web')})->group(function () {
    Route::resource('{$routePrefix}', {$controllerName}::class)->names('{$routeName}');
});
PHP;

        // Check of de route al bestaat
        $currentContent = File::get($routeFile);
        if (str_contains($currentContent, "Route::resource('{$routePrefix}'")) {
            return false;
        }

        return (bool) File::append($routeFile, $routes);
    }

    protected function appendApiRoutes(): bool
    {
        $routeFile = config('crud-generator.paths.route') . '/api.php';
        $controllerName = $this->getControllerName();
        $routePrefix = $this->getRoutePrefix();
        $routeName = "api.{$routePrefix}";

        $routes = <<<PHP


// {$this->modelName} API Routes
use App\\Http\\Controllers\\{$controllerName};

Route::middleware({$this->formatMiddleware('api')})->prefix('v1')->group(function () {
    Route::apiResource('{$routePrefix}', {$controllerName}::class)->names('{$routeName}');
});
PHP;

        // Check of de route al bestaat
        $currentContent = File::get($routeFile);
        if (str_contains($currentContent, "Route::apiResource('{$routePrefix}'")) {
            return false;
        }

        return (bool) File::append($routeFile, $routes);
    }

    protected function formatMiddleware(string $type): string
    {
        $middleware = config("crud-generator.routes.{$type}_middleware", ['web']);
        
        if (count($middleware) === 1) {
            return "'" . $middleware[0] . "'";
        }

        return "['" . implode("', '", $middleware) . "']";
    }
}
