<?php
/**
 * Template Name: Test
 *
 * @since    1.0.0
 */

get_header();

function dd_api_get_response_json($request, $params = false, $array = false) {
    $service_token = '36b3aca0648e221ca5b7b3e78def336f';
    $service_url = 'https://service.harvestmedia.net/HMP-WS.svc/';
    if (false !== $params) {
        $url = $service_url . $request . '/' . $params;
    } else {
        $url = $service_url . $request . '/' . $service_token .'/hasactivetrackonly';
    }
    $args = array(
        'timeout' => 20,
        'headers' => array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ),
        'method' => 'GET'
    );
    $response = wp_remote_request($url, $args);
    $body = wp_remote_retrieve_body($response);
    $body = dd_decode_harvest_json($body, $array);
    return $body;
}

/**
 * dd_decode_harvest_json($body)
 *
 * @param $body is the wp_remote_retrieve_body
 */
function dd_decode_harvest_json($body, $array = false) {
    for ($i = 0; $i <= 31; ++$i) {
        $body = str_replace(chr($i), "", $body);
    }
    $body = str_replace(chr(127), "", $body);

    // Some file begins with 'efbbbf' to mark the beginning of the file. (binary level)
    // here we detect it and we remove it, basically it's the first 3 characters
    if (0 === strpos(bin2hex($body), 'efbbbf')) {
        $body = substr($body, 3);
    }

    $body = json_decode($body, $array);

    return $body;
}


?>
    <div class="container">

        <?php

//        $response = dd_api_get_response_json('getcategories' );
//
//        $parent = array();
//        $child = array();
//        $level_2 = array();
//        $level_3 = array();
//        $level_4 = array();
//        foreach ( $response->Categories as $category){
//            if (!in_array($category->Name, array('Category'))) continue;
//            if (null !== $category->Attributes){
//                $parent[] = $category->Name;
//                foreach ($category->Attributes as $attr){
//                    $child[$attr->ID] = array(
//                        'parent' => $category->Name,
//                        'child' => $attr
//                    );
//                    if (property_exists( $attr,'Attributes')) {
//                        echo sprintf('<pre>%s - has child</pre>', $attr->Name);
//                    } else {
//                        echo sprintf('<pre>%s</pre>', $attr->Name);
//                    }
//                }
//            }
//        }
//
//        echo '<h3>Level 2</h3>';
//        d($child);
//        foreach ($child as $key=>$value) {
//           if (property_exists($value['child'], 'Attributes')){
//               foreach ($value['child']->Attributes as $attribute){
//                   $level_2[$attribute->ID] = array(
//                       'parent' => $value['child']->Name,
//                       'child' => $attribute
//                   );
//               }
//           }
//        }
//        d($level_2);
//
//        foreach ($level_2 as $key=>$value) {
//            if (property_exists($value['child'], 'Attributes')){
//                foreach ($value['child']->Attributes as $attribute){
//                    $level_3[$attribute->ID] = array(
//                        'parent' => $value['child']->Name,
//                        'child' => $attribute
//                    );
//                }
//            }
//        }
//        foreach ($level_3 as $key=>$value) {
//            if (property_exists($value['child'], 'Attributes')){
//                foreach ($value['child']->Attributes as $attribute){
//                    $level_4[$attribute->ID] = array(
//                        'parent' => $value['child']->Name,
//                        'child' => $attribute
//                    );
//                }
//            }
//        }
//
//d($level_3);
//        d($level_4);


        $db = new wpdb('az1_wp', 'd1L8swIkRKEl', 'az1_wp', 'localhost');
//
//        $top_level = $db->get_results("SELECT * FROM `wp_harvest_categories` WHERE parent_id LIKE '0'");


        $categories = array(
            'Category' => '479edf93cd8fbb6a',
            'Instrumentation' => 'be494e2af6c0d7c3',
            'Mood' => 'a6820296d143fd89',
            'Usage' => 'ce97a619d947784f',
            'TEMPO' => '8028fc0a3c71f280',
        );


        foreach ($categories as $name=>$id) {
            $level_2 = $level_3 = $level_4 = array();
            $first_level = $db->get_results("SELECT * from `wp_harvest_categories` WHERE parent_id like '{$id}'", 'ARRAY_A');
            foreach ($first_level as $key => $parent) {
                $result = $db->get_results("SELECT * from `wp_harvest_categories` WHERE parent_id like '{$parent['category_id']}'", 'ARRAY_A');
                if ($result) {
                    $level_2[$parent['category_id']] = $result;
                    $first_level[$key]['has_child'] = true;
                }
            }

            foreach ($level_2 as $id => $child) {
                foreach ($child as $key => $node) {
                    $result = $db->get_results("SELECT * from `wp_harvest_categories` WHERE parent_id like '{$node['category_id']}'", 'ARRAY_A');
                    if ($result) {
                        $level_2[$id][$key]['has_child'] = true;
                        $level_3[$node['category_id']] = $result;
                    }
                }
            }


            foreach ($level_3 as $id => $child) {
                foreach ($child as $key => $node) {
                    $result = $db->get_results("SELECT * from `wp_harvest_categories` WHERE parent_id like '{$node['category_id']}'", 'ARRAY_A');
                    if ($result) {
                        $level_3[$id][$key]['has_child'] = true;
                        $level_4[$node['category_id']] = $result;
                    }
                }
            }

            $menu[$name] = array(
                'level_1' => $first_level,
                'level_2' => $level_2,
                'level_3' => $level_3,
                'level_4' => $level_4
            );
        }

        get_side_menu_html($menu);

        function get_side_menu_html($menu) {
            $html = '';

            foreach ($menu as $type => $level) {
                $html .= '<div class="wrapper" id="' . $type . 'Wrapper"><div class="sub-menu" id="' . $type . '">';
                $html .= '<div class="potentialTracks">Potential Tracks: <span class="NumberOfTracks"></span></div>';
                foreach ($level['level_1'] as $item) {

                    $link = site_url('search/?search=&Category=' . $item['category_name'] . "_" . $item['category_id']);
                    if (isset($item['has_child'])) {
                        $has_child = 'true';
                        $caret = '<i class="fa fa-chevron-right"></i>';
                    } else {
                        $has_child = 'false';
                        $caret = '';
                    }
                    $html .= sprintf('<span class="categoryItem" data-totaltracks="%s"><a href="%s" class="category-id" data-categoryid="%s" data-categoryname="%s" data-objecttype="%s" data-hasChild="%s">%s</a>%s', $item['total_tracks'], $link, $item['category_id'], $item['category_name'], 'Category', $has_child, $item['category_name'], $caret);
                    $html .= '</span>';

                }
                $html .= "</div></div>";

                foreach ($level['level_2'] as $key => $value) {

                    $html .= '<div class="wrapper isChild" data-id="' . $key . '"><div class="sub-menu">';
                    $html .= '<div class="potentialTracks">Potential Tracks: <span class="NumberOfTracks"></span></div>';
                    foreach ($value as $item) {
                        $link = site_url('search/?search=&Category=' . $item['category_name'] . "_" . $item['category_id']);
                        if (isset($item['has_child'])) {
                            $has_child = 'true';
                            $caret = '<i class="fa fa-chevron-right"></i>';
                        } else {
                            $has_child = 'false';
                            $caret = '';
                        }
                        $html .= sprintf('<span class="categoryItem" data-totaltracks="%s"><a href="%s" class="category-id" data-categoryid="%s" data-categoryname="%s" data-objecttype="%s" data-hasChild="%s">%s</a>%s', $item['total_tracks'], $link, $item['category_id'], $item['category_name'], 'Category', $has_child, $item['category_name'], $caret);
                        $html .= '</span>';
                    }
                    $html .= '</div></div>';
                }

                foreach ($level['level_3'] as $key => $value) {

                    $html .= '<div class="wrapper isChild" data-id="' . $key . '"><div class="sub-menu">';
                    $html .= '<div class="potentialTracks">Potential Tracks: <span class="NumberOfTracks"></span></div>';
                    foreach ($value as $item) {
                        $link = site_url('search/?search=&' . $item['type'] . '=' . $item['category_name'] . "_" . $item['category_id']);
                        if (isset($item['has_child'])) {
                            $has_child = 'true';
                            $caret = '<i class="fa fa-chevron-right"></i>';
                        } else {
                            $has_child = 'false';
                            $caret = '';
                        }
                        $html .= sprintf('<span class="categoryItem" data-totaltracks="%s"><a href="%s" class="category-id" data-categoryid="%s" data-categoryname="%s" data-objecttype="%s" data-hasChild="%s">%s</a>%s', $item['total_tracks'], $link, $item['category_id'], $item['category_name'], 'Category', $has_child, $item['category_name'], $caret);
                        $html .= '</span>';
                    }
                    $html .= '</div></div>';
                }

                foreach ($level['level_4'] as $key => $value) {

                    $html .= '<div class="wrapper isChild" data-id="' . $key . '"><div class="sub-menu">';
                    $html .= '<div class="potentialTracks">Potential Tracks: <span class="NumberOfTracks"></span></div>';
                    foreach ($value as $item) {
                        $link = site_url('search/?search=&Category=' . $item['category_name'] . "_" . $item['category_id']);
                        if (isset($item['has_child'])) {
                            $has_child = 'true';
                            $caret = '<i class="fa fa-chevron-right"></i>';
                        } else {
                            $has_child = 'false';
                            $caret = '';
                        }
                        $html .= sprintf('<span class="categoryItem" data-totaltracks="%s"><a href="%s" class="category-id" data-categoryid="%s" data-categoryname="%s" data-objecttype="%s" data-hasChild="%s">%s</a>%s', $item['total_tracks'], $link, $item['category_id'], $item['category_name'], 'Category', $has_child, $item['category_name'], $caret);
                        $html .= '</span>';
                    }
                    $html .= '</div></div>';
                }

            }
            d($html);
            return $html;
        }
        ?>

    </div>
<?php

wp_footer();
