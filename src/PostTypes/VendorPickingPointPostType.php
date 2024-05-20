<?php

namespace Adue\LivrikaPickingPoints\PostTypes;

use Adue\WordPressBasePlugin\Modules\Registers\PostTypes\BasePostType;

class VendorPickingPointPostType extends BasePostType
{

    protected string $name = 'Vendor - Picking points';
    protected string $singularName = 'Vendor - Picking point';
    protected string $postType = 'vendor-picking_point';

    public function __construct()
    {
        $this->args['supports'] = ['title', 'custom-fields'];
    }

}