<?php

/*
Plugin Name: WPU Media Finder
Description: Organize your media library.
Plugin URI: https://github.com/WordPressUtilities/wpumediafinder
Version: 0.2.0
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
        load_plugin_textdomain('wpumediafinder', false, dirname(plugin_basename(__FILE__)) . '/lang/');
        add_action('admin_menu', array(&$this, 'add_folders_links'));
        add_filter('ajax_query_attachments_args', array(&$this, 'attachment_query'), 10, 1);
        add_filter('submenu_file', array(&$this, 'set_active_submenu'), 10, 2);
        add_filter('admin_footer-upload.php', array(&$this, 'set_current_title'), 10, 2);
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
                'upload.php?mediafolders=' . esc_attr($folder->slug)
            );
        }
    }

    public function set_active_submenu($submenu_file, $parent_file) {
        if ($parent_file == 'upload.php' && isset($_GET['mediafolders'])) {
            $submenu_file = 'upload.php?mediafolders=' . urlencode($_GET['mediafolders']);
        }
        return $submenu_file;
    }

    public function set_current_title() {
        if (!isset($_GET['mediafolders'])) {
            return;
        }
        $folder = get_term_by('slug', $_GET['mediafolders'], 'mediafolders');
        if (!isset($folder->name)) {
            return;
        }
        $name = esc_html($folder->name);

        echo "<script>
        var jQtitle = jQuery('#wp-media-grid .wp-heading-inline');
        jQtitle.text(jQtitle.text()+' - ${name}');
        </script>';";
    }
}

$WPUMediaFinder = new WPUMediaFinder();
