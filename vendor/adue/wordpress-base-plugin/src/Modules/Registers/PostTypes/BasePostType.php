<?php

namespace Adue\WordPressBasePlugin\Modules\Registers\PostTypes;

use Adue\WordPressBasePlugin\Base\Loader;
use Adue\WordPressBasePlugin\Helpers\Traits\UseLoader;
use Adue\WordPressBasePlugin\Traits\LoaderTrait;

class BasePostType
{

    use LoaderTrait;

    protected string $postType = 'custom_post_type';
    protected string $name = '';
    protected string $singularName = '';

    protected array $labels = [];

    protected array $args = [
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'book'],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => [] //['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ],
    ];

    public function register()
    {
        $this->loader->addAction('init', $this, 'registerPostType');
    }

    public function registerPostType()
    {
        register_post_type($this->postType, $this->getArgs());
    }

    private function buildLabels($label = false)
    {
        $labels = [
            'name'                  =>  $this->name,
            'singular_name'         =>  $this->singularName,
            'menu_name'             =>  $this->name,
            'name_admin_bar'        =>  $this->singularName,
            'add_new'               =>  'Add New',
            'add_new_item'          =>  'Add New '.$this->singularName,
            'new_item'              =>  'New '.$this->singularName,
            'edit_item'             =>  'Edit '.$this->singularName,
            'view_item'             =>  'View '.$this->singularName,
            'all_items'             =>  'All '.$this->name,
            'search_items'          =>  'Search '.$this->name,
            'parent_item_colon'     =>  'Parent '.$this->name.':',
            'not_found'             =>  'No '.$this->name.' found.',
            'not_found_in_trash'    =>  'No '.$this->name.' found in Trash.',
            'featured_image'        =>  $this->singularName.' Cover Image',
            'set_featured_image'    =>  'Set cover image',
            'remove_featured_image' =>  'Remove cover image',
            'use_featured_image'    =>  'Use as cover image',
            'archives'              =>  $this->singularName.' archives',
            'insert_into_item'      =>  'Insert into '.$this->singularName,
            'uploaded_to_this_item' =>  'Uploaded to this '.$this->singularName,
            'filter_items_list'     =>  'Filter '.$this->name.' list',
            'items_list_navigation' =>  $this->name.' list navigation',
            'items_list'            =>  $this->name.' list',
        ];

        if(!$label)
            return $labels;

        return $labels[$label];
    }

    private function getArgs()
    {
        $labels = $this->buildLabels();

        foreach ($labels as $label => $value) {
            $this->args['labels'][$label] = isset($this->labels[$label]) ? $this->labels[$label] : $value;
        }
        return $this->args;
    }

}