<?php

class video_module extends Unit_Module {

    var $order = 3;
    var $name = 'video_module';
    var $label = 'Video';
    var $description = '';
    var $front_save = false;
    var $response_type = '';

    function __construct() {
        $this->on_create();
    }

    function video_module() {
        $this->__construct();
    }

    function front_main($data) {
        ?>
        <div class="<?php echo $this->name; ?> front-single-module<?php echo ( $this->front_save == true ? '-save' : '' ); ?>">
            <?php if ($data->post_title != '' && $this->display_title_on_front($data)) { ?>
                <h2 class="module_title"><?php echo $data->post_title; ?></h2>
            <?php } ?>

            <?php //if ($data->post_content != '') { ?>  
                <!-- <div class="module_description"><?php // echo apply_filters('element_content_filter', apply_filters('the_content', $data->post_content)); ?></div> -->
            <?php //} ?>

            <?php if ($data->video_url != '') { ?>  
                <div class="video_player">
                    <?php
                    $video_extension = pathinfo($data->video_url, PATHINFO_EXTENSION);

                    if (!empty($video_extension)) {//it's file, most likely on the server
                        $attr = array(
                            'src' => $data->video_url,
                                //'width' => $data->player_width,
                                //'height' => 550//$data->player_height,
                        );
                        echo wp_video_shortcode($attr);
                    } else {

                        $embed_args = array(
                                //'width' => $data->player_width,
                                //'height' => 550
                        );

                        echo wp_oembed_get($data->video_url);
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    function admin_main($data) {
        global $content_width;

        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');
        wp_enqueue_media();
        wp_enqueue_script('media-upload');

        $supported_video_extensions = implode(", ", wp_get_video_extensions());

        ?>

        <div class="<?php if (empty($data)) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if (empty($data)) { ?>style="display:none;"<?php } ?>>

            <h3 class="module-title sidebar-name <?php echo ! empty($data->active_module) ? 'is_active_module' : ''; ?>" data-panel="<?php echo ! empty( $data->panel ) ? $data->panel : ''; ?>" data-id="<?php echo ! empty( $data->ID ) ? $data->ID : ''; ?>">
                <span class="h3-label">
                    <span class="h3-label-left"><?php echo ( isset($data->post_title) && $data->post_title !== '' ? $data->post_title : __('Untitled', 'cp') ); ?></span>
                    <span class="h3-label-right"><?php echo $this->label; ?></span>
                    <?php
                    parent::get_module_move_link();
                    ?>
                </span>
            </h3>

            <div class="module-content">

                <input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo ( isset($data->module_order) ? $data->module_order : 999 ); ?>" />
                <input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>" />
                <input type="hidden" name="<?php echo $this->name; ?>_id[]" value="<?php echo ( isset($data->ID) ? $data->ID : '' ); ?>" />

                <?php if (isset($data->ID)) { ?>
                    <input type="hidden" class="element_id" value="<?php echo esc_attr($data->ID); ?>" />
                <?php } else { ?>
                    <input type="hidden" class="removable" />
        <?php } ?>

                <label class="bold-label"><?php
                    _e('Element Title', 'cp');
                    $this->time_estimation($data);
                    ?></label>
                    <?php echo $this->element_title_description(); ?>
                    
                <input type="text" class="element_title" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr(isset($data->post_title) ? $data->post_title : '' ); ?>" />

                <?php echo $this->show_title_on_front_element($data);?>

                <!-- <label class="bold-label"><?php // _e('Content', 'cp'); ?></label>
                <div class="editor_in_place">
                    <?php
                    // $args = array(
                    //     "textarea_name" => $this->name . "_content[]",
                    //     "textarea_rows" => 5,
                    //     "quicktags" => false,
                    //     "teeny" => true,
                    // );
                    //
                    // $editor_id = ( esc_attr(isset($data->ID) ? 'editor_' . $data->ID : rand(1, 9999) ) );
                    // wp_editor(htmlspecialchars_decode(( isset($data->post_content) ? $data->post_content : '')), $editor_id, $args);
                    ?>
                </div> -->

                <div class="video_url_holder">
                    <label><?php _e('Put a URL or Browse for a video file.', 'cp'); ?><br />
                        <span class="element_title_description"><?php printf(__('You can enter a Youtube or Vimeo link (oEmbed support is required). Alternatively you can Browse for a file - supported video extensions (%s)', 'cp'), $supported_video_extensions); ?> </span>
                        <input class="video_url" type="text" size="36" name="<?php echo $this->name; ?>_video_url[]" value="<?php echo esc_attr(( isset($data->video_url) ? $data->video_url : '')); ?>" />
                        <input class="video_url_button" type="button" value="<?php _e('Browse', 'ub'); ?>" />
                    </label>
                </div>

                <!--<div class="video_additional_controls">

                    <label><?php _e('Player Width ( pixels )', 'cp'); ?></label>
                    <input type="text" name="<?php echo $this->name; ?>_player_width[]" value="<?php echo ( isset($data->player_width) ? esc_attr($data->player_width) : esc_attr(empty($content_width) ? 960 : $content_width ) ); ?>" />

                </div>-->
                <?php
                if (isset($data->ID)) {
                    parent::get_module_delete_link($data->ID);
                } else {
                    parent::get_module_remove_link();
                }
                ?>
            </div>

        </div>

        <?php
    }

    function on_create() {
        $this->order = apply_filters($this->name.'_order', $this->order);
        add_filter('wp_video_shortcode', array(&$this, 'cp_video_shortcode'));
        $this->description = __('Allows adding video files and video embeds to the unit', 'cp');
        $this->save_module_data();
        parent::additional_module_actions();
    }

    function cp_video_shortcode($html) {
        if (!empty($html)) {
            $out = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $html);
            $out = preg_replace('/(width|height)="\d*"\s/', "", $out);
        }
        return $out;
    }

    function save_module_data() {
        global $wpdb, $last_inserted_unit_id, $save_elements;

        if (isset($_POST['module_type']) && ( $save_elements == true )) {

            foreach (array_keys($_POST['module_type']) as $module_type => $module_value) {

                if ($module_value == $this->name) {
                    $data = new stdClass();
                    $data->ID = '';
                    $data->unit_id = '';
                    $data->title = '';
                    $data->excerpt = '';
                    $data->content = '';
                    $data->metas = array();
                    $data->metas['module_type'] = $this->name;
                    $data->post_type = 'module';

                    if (isset($_POST[$this->name . '_id'])) {
                        foreach ($_POST[$this->name . '_id'] as $key => $value) {
                            $data->ID = $_POST[$this->name . '_id'][$key];
                            $data->unit_id = ( ( isset($_POST['unit_id']) and ( isset($_POST['unit']) && $_POST['unit'] != '' ) ) ? $_POST['unit_id'] : $last_inserted_unit_id );
                            $data->title = $_POST[$this->name . '_title'][$key];
							if( ! empty( $_POST[$this->name . '_content'] ) ) {
	                            $data->content = $_POST[$this->name . '_content'][$key];
							}
                            $data->metas['module_order'] = $_POST[$this->name . '_module_order'][$key];
                            $data->metas['video_url'] = $_POST[$this->name . '_video_url'][$key];
							if( ! empty( $_POST[$this->name . '_player_width'] ) ) {
	                            $data->metas['player_width'] = $_POST[$this->name . '_player_width'][$key];								
							}
                            $data->metas['time_estimation'] = $_POST[$this->name . '_time_estimation'][$key];

                            if (isset($_POST[$this->name . '_show_title_on_front'][$key])) {
                                $data->metas['show_title_on_front'] = $_POST[$this->name . '_show_title_on_front'][$key];
                            } else {
                                $data->metas['show_title_on_front'] = 'no';
                            }
                            //$data->metas['player_height'] = $_POST[$this->name . '_player_height'][$key];

                            parent::update_module($data);
                        }
                    }
                }
            }
        }
    }

}

coursepress_register_module('video_module', 'video_module', 'output');
?>