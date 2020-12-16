<?php
/**
 * Remove unnecessary WordPress functions
 */
namespace WPStudioCode\WSP_Optimize;
use WP_Error as WP_Error;

defined( 'ABSPATH' ) or die();

class OptWPSimize {
        
    private $optimize = array();
            
    public function __construct( Array $optimizations = array() ) {

        $defaults =  array(
            'blockExternalHTTP' => false,
            'deferCSS' => false,
            'deferJS' => true,
            'disableComments' => false,
            'disableEmbed' => false,
            'disableEmoji' => true,
            'disableFeeds' => false,
            'disableHeartbeat' => false,
            'disablejQuery' => false,
            'disablejQueryMigrate' => true,
            'disableRestApi' => false,
            'disableRSD' => true,
            'disableShortlinks' => true,                       
            'disableVersionNumbers' => true,            
            'disableWLWManifest' => true,
            'disableWPVersion' => true,            
            'disableXMLRPC' => true,
            'jqueryToFooter' => true,
            'limitCommentsJS' => true,
            'limitRevisions' => true,
            'removeCommentsStyle' => true,
	    'slowHeartbeat' => true,
	    'disableGutenpoop' => true
        );
        
        $this->optimize = wp_parse_args($optimizations, $defaults);
        $this->optimize();

    }

    private function optimize() {
        foreach($this->optimize as $key => $value) {
            if( $value === true && method_exists($this, $key) ) {
                $this->$key();
            }
        }
    }
    
    private function blockExternalHTTP() {
        if( ! is_admin() ) {
            add_filter( 'pre_http_request', function() {
                return new WP_Error('http_request_failed', __('Request blocked by WPS-Optimize.'));    
            }, 100 );
        }
    }

    private function deferCSS() {

        $object = $this;
        
        add_action( 'wp_enqueue_scripts', function() use( $object ) {

            if( is_customize_preview() ) {
                return;
            }            
            
            global $wp_styles;
            
            foreach( $wp_styles->queue as $style ) {    
                $object->styles[]   = $wp_styles->registered[$style];  
                $dependencies       = $wp_styles->registered[$style]->deps;
                
                if( ! $dependencies) {
                    continue;
                }
                
                foreach( $dependencies as $dependency ) { 
                    $object->styles[] = $wp_styles->registered[$dependency];
                }  

            }
            
            $object->styles = array_unique( $object->styles, SORT_REGULAR );

            foreach( $object->styles as $style ) {
                wp_dequeue_style($style->handle);
            }            
            
        }, 9999);        
        
        add_action( 'wp_head', function() use( $object ) {

            if( is_customize_preview() ) {
                return;
            }            
         
            $output = '<script>function loadCSS(a,b,c,d){"use strict";var e=window.document.createElement("link"),f=b||window.document.getElementsByTagName("script")[0],g=window.document.styleSheets;return e.rel="stylesheet",e.href=a,e.media="only x",d&&(e.onload=d),f.parentNode.insertBefore(e,f),e.onloadcssdefined=function(b){for(var c,d=0;d<g.length;d++)g[d].href&&g[d].href.indexOf(a)>-1&&(c=!0);c?b():setTimeout(function(){e.onloadcssdefined(b)})},e.onloadcssdefined(function(){e.media=c||"all"}),e}';
            foreach( $object->styles as $style ) { 
                if( isset($style->extra['conditional'] ) ) 
                    continue;
                
                if( strpos($style->src, 'http') === false )    
                    $style->src = site_url() . $style->src;
                $output .= 'loadCSS("' . $style->src . '", "", "' . $style->args . '");';           
            }
            $output .= '</script>';
            
            echo $output;
            
        }, 9999);
        
    }    

    private function deferJS() {

        if( is_customize_preview() || is_admin() ) {
            return;
        }

        add_filter( 'script_loader_tag', function( $tag ) {
            return str_replace( ' src', ' defer="defer" src', $tag );    
        }, 10, 1 );    
    }    
    
  
    private function disableComments() {
        
        if( is_admin() ) {
            update_option( 'default_comment_status', 'closed' ); 
        }
        
        add_filter( 'comments_open', '__return_false', 20, 2 );
        add_filter( 'pings_open', '__return_false', 20, 2 );
        
        add_action( 'admin_init', function() {
            
            $post_types     = get_post_types();
            
            foreach($post_types as $post_type) {
                if (post_type_supports($post_type, 'comments') ) {
                    remove_post_type_support($post_type, 'comments');
                    remove_post_type_support($post_type, 'trackbacks');
                }
            }
            
        }); 
        
        add_action( 'admin_menu', function() {
            remove_menu_page('edit-comments.php');
        } );
        
        add_action( 'wp_before_admin_bar_render', function() {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu('comments');              
        } );              
        
    }
    
   
    private function disableEmbed() {

        add_action( 'wp_enqueue_scripts', function() {
            wp_deregister_script('wp-embed');
        }, 100 );

        add_action( 'init', function() {
        
            remove_action( 'wp_head', 'wp_oembed_add_host_js' ); 

            remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );        

            remove_action( 'rest_api_init', 'wp_oembed_register_route' );

            remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
            
            add_filter( 'embed_oembed_discover', '__return_false' );            
            
        });
        
    }     
    

    private function disableRestApi() {
        
        remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
        remove_action( 'rest_api_init', 'wp_oembed_register_route' );
        add_filter( 'embed_oembed_discover', '__return_false' );
        remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
        remove_action( 'wp_head', 'wp_oembed_add_host_js' );
        remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );


        add_filter('json_enabled', '__return_false');
        add_filter('json_jsonp_enabled', '__return_false');
        add_filter('rest_enabled', '__return_false');
        add_filter('rest_jsonp_enabled', '__return_false');      
        
    }     
         

    private function disableEmoji() {
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        
        add_filter( 'tiny_mce_plugins', function( $plugins ) {
            if ( ! is_array($plugins) ) {
                return array();
            }
            return array_diff($plugins, array('wpemoji'));            
        }, 10, 1 );       
    }    
      

    private function disableFeeds() {        
        remove_action( 'wp_head', 'feed_links_extra', 3 ); 
        remove_action( 'wp_head', 'feed_links', 2 );   
        add_action( 'do_feed', array($this, 'disableFeedsHook'), 1 );
        add_action( 'do_feed_rdf', array($this, 'disableFeedsHook'), 1 );
        add_action( 'do_feed_rss', array($this, 'disableFeedsHook'), 1 );
        add_action( 'do_feed_rss2', array($this, 'disableFeedsHook'), 1 );
        add_action( 'do_feed_atom', array($this, 'disableFeedsHook'), 1 );        
    }  
    

    public function disableFeedsHook() {
        wp_die( '<p>' . __('Feed disabled by WP Optimize.') . '</p>' );
    }
    

    private function disableHeartbeat() {
        add_action('admin_enqueue_scripts', function() {
            wp_deregister_script('heartbeat');    
        });
    }
    
   
    private function disablejQuery() {
        add_action( 'wp_enqueue_scripts', function() {
            wp_deregister_script('jquery');
        }, 100 );
    }     

   
    private function disablejQueryMigrate() {

        add_filter( 'wp_default_scripts', function( $scripts ) {
            if( ! empty($scripts->registered['jquery']) ) {
                $scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
            }
        } );

    }
  

    private function disableRSD() { 
        remove_action('wp_head', 'rsd_link'); 
    }     
    

    private function disableShortlinks() { 
        remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );        
    }    
    

    private function disableVersionNumbers() {
        add_filter( 'style_loader_src', array($this, 'disableVersionNumbersHook'), 9999 );
        add_filter( 'script_loader_src', array($this, 'disableVersionNumbersHook'), 9999 ); 
    }
    

    public function disableVersionNumbersHook( $target_url = '' ) {
        
        if( strpos( $target_url, 'ver=' ) ) {
            $target_url = remove_query_arg( 'ver', $target_url );
        }
        
        return $target_url;
        
    }
    

    private function disableWLWManifest() {
        remove_action('wp_head', 'wlwmanifest_link');   
    }   
       

    private function disableWPVersion() {
        remove_action( 'wp_head', 'wp_generator' ); 
        add_filter( 'the_generator', '__return_null' ); 
    }  
    

    private function disableXMLRPC() {
        
        if( is_admin() ) {
            update_option( 'default_ping_status', 'closed' ); 
        }
        
        add_filter( 'xmlrpc_enabled', '__return_false' ); 
        add_filter( 'pre_update_option_enable_xmlrpc', '__return_false' );
        add_filter( 'pre_option_enable_xmlrpc', '__return_zero' );       
        

        add_filter( 'wp_headers', function( $headers ) {
            if( isset( $headers['X-Pingback'] ) ) {
                unset( $headers['X-Pingback'] );
            }
            return $headers;              
        }, 10, 1 );
        
 
        add_filter( 'xmlrpc_methods', function( $methods ) {
            unset( $methods['pingback.ping'] );
            unset( $methods['pingback.extensions.getPingbacks'] );
            return $methods;
        }, 10, 1  );
        
    }    


    private function jqueryToFooter() {
        add_action( 'wp_enqueue_scripts', function() {
            wp_deregister_script( 'jquery' );
            wp_register_script( 'jquery', includes_url( '/js/jquery/jquery.js' ), false, NULL, true );
            wp_enqueue_script( 'jquery' );           
        } );    
    }


    private function limitCommentsJS() {
        
        add_action('wp_print_scripts', function() {
            if(is_singular() && (get_option('thread_comments') == 1) && comments_open() && get_comments_number() ) {
                wp_enqueue_script('comment-reply');     
            } else {
                wp_dequeue_script('comment-reply');
            }           
        }, 100);

    }
    

    private function limitRevisions() {

        if( defined('WP_POST_REVISIONS') && (WP_POST_REVISIONS != false) ) {
            add_filter( 'wp_revisions_to_keep', function( $num, $post) {
                return 5;
            }, 10, 2 );
        } 

    }


    private function removeCommentsStyle() {    
        add_action( 'widgets_init', function() {
            global $wp_widget_factory;
            remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
        });  
    }  


    private function slowHeartbeat() {
        
        add_filter( 'heartbeat_settings', function($settings) {
            $settings['interval'] = 60; 
            return $settings;
        } );

    }   

    private function disableGutenpoop(){
	    add_filter('use_block_editor_for_post', '__return_false', 10);
	    add_filter('use_block_editor_for_post_type', '__return_false', 10);
    } 
      
}
