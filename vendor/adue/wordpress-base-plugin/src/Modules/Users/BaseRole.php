<?php

namespace Adue\WordPressBasePlugin\Modules\Users;

use Adue\WordPressBasePlugin\Traits\LoaderTrait;

class BaseRole
{

    use LoaderTrait;

    protected string $role = 'base_role';
    protected string $displayName = 'Base role';
    protected array $capabilities = [];

    public function add()
    {
        $this->loader()->addAction('init', $this, 'register');
    }

    public function remove()
    {
        $this->loader()->addAction('init', $this, 'deregister');
    }

    public function register()
    {
        add_role(
            $this->role,
            $this->displayName,
            $this->capabilities,
        );
    }

    public function deregister()
    {
        remove_role(
            $this->role
        );
    }

}