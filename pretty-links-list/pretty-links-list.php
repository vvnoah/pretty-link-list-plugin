<?php
/**
 * @package pretty-links-list
*/

/*
Plugin Name: Pretty Links List
Plugin URI: http://localhost/wp-development
Description: Displays the links from Pretty Links in a table.
Version: 1.0.0
Author: Noah Van Vaerenbergh
Author URI: http://localhost/wp-development
License: GPLv2 or later
Text Domain: pretty-links-list
*/

// This if statement includes the WP_List_Table object in this plugin.
// This object is used to create a nice looking table with pages, etc..
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

// This class includes everything used to retrieve data from the Pretty Link database,
// format that data, insert it into the table and then build the table.
class List_Table extends WP_List_Table {

    function __construct(){
        global $status,$page;

        parent::__construct(array(
            'singular'=>'link',
            'plural'=>'links',
            'ajax'=>false
        ));
    }

    // This function returns the item of the $data array that corresponds 
    // to the column name. This is how the array is split into columns.
    function column_default($item, $column_name){
        switch($column_name){
            case 'slug':
            case 'url':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting.
        }
    }

    // This function returns an array with the title and key
    // of the column.
    function get_columns(){
        $columns = array(
            'slug'=> 'Redirect url (localhost/wp-development/...)',
            'url'=> 'Target url'
        );
        return $columns;
    }

    function prepare_items(){
        global $wpdb;
        
        $per_page = 12;

        $columns=$this->get_columns();
        $hidden=array();
        
        $this->_column_headers = array($columns, $hidden);

        $query = "SELECT * FROM {$wpdb->prefix}prli_links";

        $total_items = $wpdb->query($query);

        // A new stdClass is made, the Pretty Link table is queried and the data is put in the $json variable.
        // The $json variable is then decoded and turned into an array. 
        $json = new stdClass();
        $json = $wpdb->get_results($query);
        $data = json_decode(json_encode($json), true);

        $current_page = $this->get_pagenum();
        
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );
        
        //echo print_r($this->items, true); //echo whole array for troubleshooting.
    }
}

// This function creates a menu item and page. It displays it to the Author role.
// It then calls the render page function.
function add_menu_items(){
    add_menu_page('Link List', 'Link List', 'delete_published_posts', 'link-list-page', 'render_list_page', 'dashicons-admin-links');
}add_action('admin_menu', 'add_menu_items');

// This function renders the page, it creates a list table, calls the prepare_items function
// and displays the table. The table is wrapped in a div with class "wrap", so it doesn't clip through the side of the page.
function render_list_page(){
    $ListTable = new List_Table();
    $ListTable->prepare_items();

    ?>
    <div class="wrap">
        <?php $ListTable->display() ?>
    </div>
    <?php
}