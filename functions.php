<?php

add_action( 'wp_enqueue_scripts', 'dd_child_theme_enqueue_styles' );
function dd_child_theme_enqueue_styles() {

    wp_enqueue_style( 'theme-bootstrap', get_stylesheet_directory_uri(). '/bootstrap/bootstrap.min.css', array(), '4.3.1', 'all');
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/custom.css', array( 'theme-bootstrap' ), filemtime(get_stylesheet_directory() . '/custom.css')
    );

}
add_filter( 'woocommerce_enqueue_styles', 'dd_dequeue_woo_styles' );
function dd_dequeue_woo_styles( $enqueue_styles ) {
    unset( $enqueue_styles['woocommerce-general'] );	// Remove the gloss
    return $enqueue_styles;
}

// Uncomment to remove CPT from Parent Theme
/*function remove_parent_theme_cpts(){
    remove_action('init', 'my_post_type_header_image');
}
add_action('after_setup_theme', 'remove_parent_theme_cpts'); */
