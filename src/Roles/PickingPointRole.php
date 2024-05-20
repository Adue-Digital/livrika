<?php

namespace Adue\LivrikaPickingPoints\Roles;

use Adue\WordPressBasePlugin\Modules\Users\BaseRole;

class PickingPointRole extends BaseRole
{
    protected string $role = 'picking_point';
    protected string $displayName = 'Punto de retiro';
}