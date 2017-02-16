<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: Set Bulk Post Categories
Description: Allows user to set the desired categories as well as unset the categories of all the posts in a bulk without editing the posts itself.
Version:     1.0
Author:      Saurab Adhikari
Author URI:  http://saurabadhikari.com.np
Domain Path: /languages
Text Domain: set-bulk-post-categories

Set Bulk Post Categories is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
                               
Set Bulk Post Categories is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Set Bulk Categories. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/

add_action('admin_print_styles', 'sbc_stylesheet');
function sbc_stylesheet() 
{
    
    wp_enqueue_style( 'myCSS', plugins_url( '/css/style.css', __FILE__ ) );
    
}

add_action( 'admin_menu', 'sbc_menu_page' );
if(isset($_POST['setcats'])){
    if(isset($_POST['cat'])){
      global $cati;
      global $parts;
      $catids=$_POST['cat'];
      foreach ($catids as $catid) {                  
        $pids[] = substr($catid, strpos($catid, "-") + 1);
        $pid=array_unique($pids);
        $cati[]=$catid;

    }

    $new = array();

    $count = 0;
    if( empty( $playerlist ) )
    {
        foreach($cati as $key=>$value) {
            $val = explode('-', $value);
            $k = $val[1];
            if (array_key_exists( $k, $new) ) {

                $new[$k][$count] = $val[0];
            } else {

                $new[$k] = array();
                $new[$k][$count] = $val[0];
            }

            $count++;
        }

    }

            foreach ($new as $key => $value) {  //get $key as post_id & values as category_id

               wp_set_post_categories( $key, $new[$key], false );

           }


       }        

   }   


   if (!function_exists('sbc_SetCats')):
    function sbc_SetCats () {
        ?>
        <div class="wrap">
            <?php 

        }
        endif;

        if (!function_exists('wp_delete_post_link')):

            function wp_delete_post_link($link = 'Delete This', $before = '', $after = '')
        {
            global $post;
            if ( $post->post_type == 'post' ) {
                if ( !current_user_can( 'edit_post', $post->ID ) )
                    return;
            } else {
                if ( !current_user_can( 'delete_post', $post->ID ) )
                    return;
            }
            $link = "<a href='" . wp_nonce_url( get_bloginfo('url') . "/wp-admin/post.php?action=delete&amp;post=" . $post->ID, 'delete-post_' . $post->ID) . "'>".$link."</a>";
            echo $before . $link . $after;
        }

        endif;


        if (!function_exists('sbc_menu_page')):
            function sbc_menu_page() {
              add_menu_page( 'Set Bulk Post Categories', 'Set Bulk Post Categories', 'manage_options', 'set-bulk-post-categories', 'sbc_custom_function', '', 6 );

          }
          endif;


          if(!function_exists('sbc_custom_function')): 
              function sbc_custom_function() { ?>
          <?php 

          $args = array(
            'posts_per_page' => -1,
            'orderby'        => 'title' 

            );

          $query = new WP_Query($args);

          if ( $query->have_posts() ) {
            print "<h1>FOUND POSTS</h1>";
        } 
        else{

            echo '<b>'."No Posts Found.".'</b>';
        }

        ?>
        <form action="" enctype="multipart/form-data" method="post" >
            <?php        
            while ( $query->have_posts() ) {
                global $post;
                $query->the_post();
                $args = array(
                    'hide_empty'               => 0,
                    'pad_counts'               => false,
                    'orderby'                  => 'title' 
                    ); 
                echo '<br />';
                echo '<div class="postname">'.'<strong>'."Post Title: ".'</strong>'.'<input type="text" name="post[]" value="'.get_the_title($post->ID).'" readonly>'.'</div>'.'<br />';
                $categories = get_categories( $args );
                $category = get_the_category($post->ID);
                $a = array();
                $b = array();

                foreach ($categories as $cat) {
                    $a[]= $cat->term_taxonomy_id; 
                }
                foreach ($category as $cato) {
                    $b[]=$cato->term_taxonomy_id;

                } 

                $results = array_merge(array_diff($a, $b));
                $result = array_unique($results);
                echo '<h3>'.'Categories:'.'</h3>';
                foreach ($result as $resu) { //check to see if the category has been already assigned or not & chekbox is set 'unchecked' if true
                $ancestors=get_ancestors($resu,'category');
                if($ancestors){
                    echo "<span class='parents'>".get_category_parents($resu,false, ' &raquo; ')."</span>"."<span class='child'>".'<strong>'.get_cat_name($resu).'</strong>'."</span>".": <input type='checkbox' name='cat[]' value='".$resu.'-'.$post->ID."'>".'<br />';
                }
                else{
                    echo '<strong>'.get_cat_name($resu).'</strong>'.": <input type='checkbox' name='cat[]' value='".$resu.'-'.$post->ID."'>".'<br />';
                }
            }
            $res=array_intersect($a, $b);
                    foreach($res as $re) {      //check to see if the category has been already assigned & chekbox is set checked if true
                        $r=$re;
                        $ancestors1=get_ancestors($r,'category');
                        if($ancestors1){
                            echo "<span class='parents'>".get_category_parents($r,false, ' &raquo; ')."</span>"."<span class='child'>".'<strong>'.get_cat_name($r).'</strong>'."</span>".": <input type='checkbox' name='cat[]' value='".$r.'-'.$post->ID."' checked>".'<br />';
                        }
                        else{
                            echo '<strong>'.get_cat_name($r).'</strong>'.": <input type='checkbox' name='cat[]' value='".$r.'-'.$post->ID."' checked>".'<br />';
                        }

                    }   
                    echo '<br />';        
                echo '<b>'.wp_delete_post_link('Delete This').'</b>'.'&nbsp'; //link to delete the post
                echo '<b>'.edit_post_link('Edit This', '', '', $post->ID ).'</b>'.'<br />'; //link to edit the post
                echo '<br />';
                echo '<hr size="5px">';
            }
            echo "<br />"."<br />";
            echo '<input type="submit" name="setcats" value="Submit">';
            echo '</form>';
        }
        endif;