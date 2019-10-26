<?php

/*
Plugin Name: WPU Media Finder
Description: Organize your media library.
Plugin URI: https://github.com/WordPressUtilities/wpumediafinder
Version: 0.1.0
Author: Darklg
Author URI: http://darklg.me/
License: MIT License
License URI: http://opensource.org/licenses/MIT
Contributors: @ScreenFeedFr
*/

class WPUMediaFinder {
    public function __construct() {
        add_filter('plugins_loaded', array(&$this, 'plugins_loaded'));
        add_filter('plugins_loaded', array(&$this, 'create_taxonomy'));
    }

    public function plugins_loaded() {
        add_action('admin_menu', array(&$this, 'add_folders_links'));
        add_filter('ajax_query_attachments_args', array(&$this, 'attachment_query'), 10, 1);
    }

    public function create_taxonomy() {
        register_taxonomy('mediafolders', array('attachment'), array(
            'hierarchical' => true,
            'labels' => array(
                'name' => __('Folders', 'wpumediafinder'),
                'singular_name' => __('Folder', 'wpumediafinder'),
                'search_items' => __('Search Folders', 'wpumediafinder'),
                'all_items' => __('All Folders', 'wpumediafinder'),
                'parent_item' => __('Parent Folder', 'wpumediafinder'),
                'parent_item_colon' => __('Parent Folder:', 'wpumediafinder'),
                'edit_item' => __('Edit Folder', 'wpumediafinder'),
                'update_item' => __('Update Folder', 'wpumediafinder'),
                'add_new_item' => __('Add New Folder', 'wpumediafinder'),
                'new_item_name' => __('New Folder Name', 'wpumediafinder'),
                'menu_name' => __('Folders', 'wpumediafinder')
            ),
            'show_ui' => true,
            'show_admin_column' => false,
            'query_var' => false,
            'rewrite' => false
        ));

    }

    public function attachment_query($query = array()) {
        if (isset($_REQUEST['query'], $_REQUEST['query']['mediafolders'])) {
            $query['tax_query'] = array(
                array(
                    'taxonomy' => 'mediafolders',
                    'field' => 'slug',
                    'terms' => $_REQUEST['query']['mediafolders']
                )
            );
        }
        return $query;
    }

    public function add_folders_links() {
        $folders = get_terms('mediafolders', array(
            'hide_empty' => false
        ));
        foreach ($folders as $folder) {
            add_submenu_page(
                'upload.php',
                '- ' . esc_html($folder->name),
                '- ' . esc_html($folder->name),
                'upload_files',
                admin_url('upload.php') . '?mediafolders=' . esc_attr($folder->slug)
            );
        }
    }
}

$WPUMediaFinder = new WPUMediaFinder();
