<?php

namespace Adue\WordPressBasePlugin\Modules\Api;

use Adue\WordPressBasePlugin\Traits\LoaderTrait;

class BaseApiEndpoint
{

    use LoaderTrait;

    protected string $namespace = '';
    protected string $uri = '';
    protected array $methods = ['GET'];

    private array $availableMethods = ['GET', 'POST', 'PUT', 'PATCH'];

    public function handle($request)
    {}

    public function init(): void
    {
        $this->loader()->addAction('rest_api_init', $this, 'register', 10, 0 );
    }

    public function register(): void
    {
        register_rest_route(
            $this->namespace,
            $this->uri,
            [
                'methods' => $this->methods,
                'callback' => [$this, 'handle']
            ]
        );
    }

}