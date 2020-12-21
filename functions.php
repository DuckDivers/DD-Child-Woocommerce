<?php
//require get_stylesheet_directory() . '/customizer/customizer-framework.php';

add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
 
    $parent_style = '_s-style'; // This is the name of the stylesheet for DuckDiverFramework
 
    //wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.min.css' );
    wp_enqueue_style( 'theme-woocommerce', get_stylesheet_directory_uri(). '/css/woocommerce.min.css', array(), '4.3.1', 'all');
    wp_enqueue_style( 'bootstrap', get_stylesheet_directory_uri(). '/bootstrap/bootstrap.min.css', array(), '4.3.1', 'all');
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/custom.css',
        array( $parent_style ),
        filemtime(get_stylesheet_directory() . '/custom.css')
    );
}

// Uncomment to remove CPT from Parent Theme
/*function remove_parent_theme_cpts(){
    remove_action('init', 'my_post_type_header_image');
}
add_action('after_setup_theme', 'remove_parent_theme_cpts'); */
