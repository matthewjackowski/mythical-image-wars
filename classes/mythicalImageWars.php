<?php
/*
 * Class Name: Mythical Image Wars
 * Description: Main plugin class
 * Author: Matthew Jackowski
 * Version: 0.4.0
 * Author URI: http://www.linkedin.com/pub/matthew-jackowski/6/6b2/242
 * License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
 */
?>
<?php
class mythicalImageWars {
    // Soapbox time!  There's scads of articles and posts and whatnot written about this "type" of class
    // for me too it *feels* a little weird...but let's go back to understand the goal of the plugin class structure
    // so 2 things here: 1 we need *some* way to define the plugin that avoids namespace collisions
    // 2 if we have private data (which we do) we want a singleton like guarantee that we don't have multiple plugin objects floating around with different infos
    // so I borrowed from a number of different suggestions to come up with the solution below, 
    // I feel it maintains the OO intent but still smells very PHPish with liberal use of "static"
    
        private static $debug_mode = false; // true = on

        static function getDebugMode () {
            return self::$debug_mode;
        }

        static function &init() {
             mythicalImageWarsDebug::logTrace();
            // Initialize private data
            $options = get_option('debug_section');
            self::$debug_mode = $options['mode_toggle'];

        static $instance = array();
        if ( !$instance ) {
            $instance[0] = new mythicalImageWars;
        }

        return $instance[0];
    }
        function activatePlugin( ) {
            /* Do Stuff */
    }
        function deactivatePlugin( ) {
            /* Do Stuff */
    }
    function mythicalImageWars () {
            // This is our static "constructor" it adds all of the hooks to Wordpress
            mythicalImageWarsDebug::logTrace();
            // Hooks for rendering methods
            add_shortcode( 'miw-render', array('mythicalImageWars','render' ));
            add_shortcode( 'miw-rendervoting', array('mythicalImageWars','renderVotingForm' ));

            // Admin Stufs
            global $wp_rewrite;
            add_action('admin_menu',array(&$this,'createAdminMenuItem'));
            add_action('admin_init', array (&$this,'settingPageInit'));
            add_filter('attachment_fields_to_edit', array(&$this,'addImageMetaFields'), 10, 2);
            add_filter('attachment_fields_to_save', array(&$this,'addImageMeta'), 10, 2);
            add_filter( 'wp_update_attachment_metadata', array(&$this,'addImageEntryNumber'), 10, 2);
            add_action( 'admin_print_styles',array(&$this,'renderEntryNumberColumnCss'));
            add_filter( 'manage_media_columns', array(&$this,'renderEntryNumberColumnLabel'), 10, 2 );
            add_action( 'manage_media_custom_column', array(&$this,'renderEntryNumberColumn'), 10, 2 );
    //        add_filter('comments_rewrite_rules',function ($comments_rewrite) {print_r($comments_rewrite); return $comments_rewrite;},10,1);

            
            // Test Error Debug
            //timezone_open(1202229163);
        }
        // BEGIN Page Rendering method(s)
    public static function render( $args, $content = null ){
            mythicalImageWarsDebug::logTrace();
            extract( shortcode_atts( array(
                //localize attributes
                'postid' => get_the_ID()                // If no postid is given to override, use the one from the Loop
                ), $args ) );
                //get images from post...First build param array for 'get_children'
            $getChildrenParams = array(
                'numberposts' => -1,                    // Get all attached images
                'meta_key' => '_miw_entry_number',      // Define custom meta to use for sorting
                'orderby' => 'meta_value_num',          // Use the custom metato order return set
                'order'=> 'ASC',                        // Direction for sorting
                'post_mime_type' => 'image',
                'post_parent' => $postid,
                'post_status' => null,                  // Not sure if this is needed or not...but makes sense that there is no status...its an image
                'post_type' => 'attachment'
            );
            // execute get_children
            $images = get_children( $getChildrenParams );
            $miwImages = array();
            mythicalImageWarsDebug::logTrace ($images);
            if ($images) {
                
                $firstPlaceIndex =0;
                $secondPlaceIndex=0;
                $thirdPlaceIndex=0;
                foreach ($images as $image) {
                    $imageMeta = get_post_custom($image->ID);
                    mythicalImageWarsDebug::logTrace($imageMeta);
                    // Get data from Wordpress functions
                    $imageAttachmentMeta = unserialize(array_shift($imageMeta['_wp_attachment_metadata'])); // wow...nasty...thanks WP devs
                    $options = get_option('main_section');
                    if (!is_array($options))
                        $options = array($options); // weird code to fix initial states where there are no options
                    if (array_key_exists('_miw_place',$imageMeta)) {
                        $placeIndex = array_shift($imageMeta['_miw_place']);
                    } else {
                        $placeIndex = "notset";
                    }
                    $entryNumber = array_shift($imageMeta['_miw_entry_number']);
                    $attachmentImageSrcFull = wp_get_attachment_image_src( $image->ID,'full');
                    $attachmentImageSrcThumbnail = wp_get_attachment_image_src( $image->ID,'thumbnail');
                    $attachmentImageSrcMedium = wp_get_attachment_image_src( $image->ID,'medium');
                    $attachmentImageSrcPostThumbnail = wp_get_attachment_image_src( $image->ID,'post-thumbnail');
                    $attachmentImageSrcLargeFeature = wp_get_attachment_image_src( $image->ID,'large-feature');
                    $attachmentImageSrcSmallFeature = wp_get_attachment_image_src( $image->ID,'small-feature');

                    // Build our data for rendering - this new to rebuild to a manageable array strucutre
                    $miwImage = array (
                        'id' => $image->ID,
                        'title' => $image->post_title,
                        'entry_number' => $entryNumber,
                        'entry_number_padded' => str_pad($entryNumber,3,'0',STR_PAD_LEFT),
                        'html_blurb_before' => array_key_exists('_miw_html_blurb_before',$imageMeta)?array_shift($imageMeta['_miw_html_blurb_before']):null,
                        'html_blurb_after' => array_key_exists('_miw_html_blurb_after',$imageMeta)?array_shift($imageMeta['_miw_html_blurb_after']):null,
                        'place_image_src' => array_key_exists($placeIndex.'_place_image_url',$options)?$options[$placeIndex.'_place_image_url']:null,
                        'place_image_css' => array_key_exists('_miw_place_image_css',$imageMeta)?array_shift($imageMeta['_miw_place_image_css']):'display:block;position:absolute;width:117px;height:148px;',
                        'original_src' => $attachmentImageSrcFull[0],
                        'original_width' => $attachmentImageSrcFull[1],
                        'original_height' => $attachmentImageSrcFull[2],
                        'thumbnail_src' => $attachmentImageSrcThumbnail[0],
                        'thumbnail_width' => $attachmentImageSrcThumbnail[1],
                        'thumbnail_height' => $attachmentImageSrcThumbnail[2],
                        'medium_src' => $attachmentImageSrcMedium[0],
                        'medium_width' => $attachmentImageSrcMedium[1],
                        'medium_height' => $attachmentImageSrcMedium[2],
                        'post_thumbnail_src' => $attachmentImageSrcPostThumbnail[0],
                        'post_thumbnail_width' => $attachmentImageSrcPostThumbnail[1],
                        'post_thumbnail_height' => $attachmentImageSrcPostThumbnail[2],
                        'large_feature_src' => $attachmentImageSrcLargeFeature[0],
                        'large_feature_width' => $attachmentImageSrcLargeFeature[1],
                        'large_feature_height' => $attachmentImageSrcLargeFeature[2],
                        'small_feature_src' => $attachmentImageSrcSmallFeature[0],
                        'small_feature_width' => $attachmentImageSrcSmallFeature[1],
                        'small_feature_height' => $attachmentImageSrcSmallFeature[2],  
                        'exif_aperture' => $imageAttachmentMeta['image_meta']['aperture'],
                        'exif_credit' => $imageAttachmentMeta['image_meta']['credit'],
                        'exif_camera' => $imageAttachmentMeta['image_meta']['camera'],
                        'exif_caption' => $imageAttachmentMeta['image_meta']['caption'],
                        'exif_created_timestamp' => $imageAttachmentMeta['image_meta']['created_timestamp']==0?null:$imageAttachmentMeta['image_meta']['created_timestamp'],
                        'exif_copyright' => $imageAttachmentMeta['image_meta']['copyright'],
                        'exif_focal_length' => $imageAttachmentMeta['image_meta']['focal_length']==0?null:$imageAttachmentMeta['image_meta']['focal_length'],
                        'exif_iso' => $imageAttachmentMeta['image_meta']['iso']==0?null:$imageAttachmentMeta['image_meta']['iso'],
                        'exif_shutter_speed' => $imageAttachmentMeta['image_meta']['shutter_speed']==0?null:$imageAttachmentMeta['image_meta']['shutter_speed'],
                        'exif_title' => $imageAttachmentMeta['image_meta']['title']
                     );
                // The tricky bit here is that we keep indexs for first,second,third since there could be n images flagged with these attributes
                // This implementation is concrete for the 3 states, it would be a fun exercise to refactor for n states
                // Also we use PHP specific array functions (merge and splice) to insert at our index...there is fun to be had there too
                switch ($placeIndex) {
                    case "first":
                     $miwImages = array_merge(array_slice($miwImages, 0,$firstPlaceIndex), 
                         array($miwImage), 
                         array_slice($miwImages,$firstPlaceIndex)
                        );
                        $firstPlaceIndex = $firstPlaceIndex +1;
                        $secondPlaceIndex = $secondPlaceIndex +1;
                        $thirdPlaceIndex = $thirdPlaceIndex +1;
                        break;
                    case "second":
                        $miwImages = array_merge(array_slice($miwImages, 0,$secondPlaceIndex), 
                         array($miwImage), 
                         array_slice($miwImages,$secondPlaceIndex) 
                         );
                        $secondPlaceIndex = $secondPlaceIndex +1;
                        $thirdPlaceIndex = $thirdPlaceIndex +1;
                        break;
                    case "third":
                        $miwImages = array_merge(array_slice($miwImages, 0,$thirdPlaceIndex), 
                         array($miwImage), 
                         array_slice($miwImages,$thirdPlaceIndex) 
                         );
                        $thirdPlaceIndex = $thirdPlaceIndex +1;
                        break;
                    default:
                        array_push($miwImages, $miwImage);
                 
            } // End Switch
                    
                    } // end foreach image
                    
            } else {
                mythicalImageWarsDebug::logTrace('Did not return any images for Post ID:'.$postid);
            } // End If Images Check
            mythicalImageWarsDebug::logTrace ($miwImages);
 
            include_once( WP_PLUGIN_DIR .'/mythical-image-wars/templates/imagelist.php');
            $content = $content.templateImageList($miwImages);

            return $content;
        }
        public static function renderVotingForm ( $args, $content = null){
            mythicalImageWarsDebug::logTrace();
            extract( shortcode_atts( array(
                //localize attributes
                'postid' => get_the_ID()                // If no postid is given to override, use the one from the Loop
                ), $args ) );
            wp_register_style( 'votingFormStyle', plugins_url('votingform.css', WP_PLUGIN_DIR.'/mythical-image-wars/templates/css/votingform.css') );
            wp_enqueue_style( 'votingFormStyle' );

            //include_once( WP_PLUGIN_DIR .'/mythical-image-wars/templates/renderhtml-votingform.php');
            return $content;
        }
        // END Page Rendering method(s)
        // BEGIN Admin screen logix
        function addImageEntryNumber ($data, $post_id ){
           update_post_meta($post_id, '_miw_entry_number', self::calcImageEntryNumber($post_id));
           return $data; // do nothing as a filter
        }
        function calcImageEntryNumber ($imageId) {
            mythicalImageWarsDebug::logTrace();
            // Given the image/attachment post id...figure out what the next number should be
            // Note: images not attached to a post will sequence globally
            // While we try to avoid direct queries and use wordpress functions...this query gets our autonumber from our custom field
            // Since it's specific to our plugin it's not too bad...should refactor this out to it's own method however
            global $wpdb;
            $siblingImages = get_children("post_parent=".get_post($imageId)->post_parent."&post_type=attachment&post_mime_type=image");
            $stringSiblingImages = implode(array_keys($siblingImages),",");
            mythicalImageWarsDebug::logTrace("select MAX(CONVERT(meta_value,UNSIGNED INTEGER)) from $wpdb->postmeta where meta_key ='_miw_entry_number' AND post_id IN (".$stringSiblingImages.")");
            $max_count = $wpdb->get_var( $wpdb->prepare( "select MAX(CONVERT(meta_value,UNSIGNED INTEGER)) from $wpdb->postmeta where meta_key ='_miw_entry_number' AND post_id IN (%s)",$stringSiblingImages ) );
            return $max_count+1;
        }
       function addImageMeta($post, $attachment) {
            mythicalImageWarsDebug::logTrace();
           if ( substr($post['post_mime_type'], 0, 5) == 'image' ) {
                if (isset($attachment['miw_entry_number']))
                     update_post_meta($post['ID'], '_miw_entry_number', $attachment['miw_entry_number']);
                if (isset($attachment['miw_place']))
                     update_post_meta($post['ID'], '_miw_place', $attachment['miw_place']);
                if (isset($attachment['miw_place_image_css']))
                     update_post_meta($post['ID'], '_miw_place_image_css', $attachment['miw_place_image_css']);
                if (isset($attachment['miw_html_blurb_before']) && $attachment['miw_html_blurb_after']!=null)
                     update_post_meta($post['ID'], '_miw_html_blurb_before', $attachment['miw_html_blurb_before']);
                if (isset($attachment['miw_html_blurb_after']) && $attachment['miw_html_blurb_after']!=null)
                     update_post_meta($post['ID'], '_miw_html_blurb_after', $attachment['miw_html_blurb_after']);
           }
           return $post;
       }
       function addImageMetaFields($form_fields, $post) {
        mythicalImageWarsDebug::logTrace();
    if ( substr($post->post_mime_type, 0, 5) == 'image' ) {
        $miw_entry_number = get_post_meta($post->ID, '_miw_entry_number', true);
        if ( empty($miw_entry_number ) ){
                    $miw_entry_number  = self::calcImageEntryNumber($post->ID);
                }
        $form_fields['miw_entry_number'] = array(
            'value' => $miw_entry_number,
            'label' => __('Entry Number'),
            'helps' => __('*Added by the MIW Plugin: This field is auto-incremented, but you can override it here.')
        );
                /*
               $miw_judge_rating = get_post_meta($post->ID, '_wp_attachment_image_miw_judge_rating', true);
        if ( empty($miw_judge_rating) )
            $miw_judge_rating  = '';
                $form_fields['miw_judge_rating'] = array(
            'value' => $miw_judge_rating,
            'label' => __('Judge Rating'),
            'helps' => __('Alt text for the image, e.g. &#8220;The Mona Lisa&#8221;')
        );
                $miw_public_rating = get_post_meta($post->ID, '_wp_attachment_image_miw_public_rating', true);
        if ( empty($miw_public_rating) )
            $miw_public_rating  = '';
               $form_fields['miw_public_rating'] = array(
            'value' => $miw_public_rating,
            'label' => __('Public Rating'),
            'helps' => __('Alt text for the image, e.g. &#8220;The Mona Lisa&#8221;')
        );
                 */
             
               $form_fields['miw_place'] = array (
                        'label' => __('Place in Contest'),
            'helps' => __('*Added by the MIW Plugin: Place value overrides entry ordinal and overlays place image'),
                        'input' => 'html',
            'html'  => mythicalImageWars::renderPlaceInputFields($post, get_option('miw_place'))
               );
               $miw_place_image_css = get_post_meta($post->ID, '_miw_place_image_css', true);
        if ( empty($miw_place_image_css ) )
            $miw_place_image_css  = 'display:block;position:absolute;width:117px;height:148px;';
                $form_fields['miw_place_image_css'] = array (
                        'value' => $miw_place_image_css,
                        'label' => __('CSS for Place Overlay Image'),
            'helps' => __('*Added by the MIW Plugin: Sets the CSS value for the place overlay image...this is useful primarily for positioning'),

               );   
                $miw_html_blurb_before = get_post_meta($post->ID, '_miw_html_blurb_before', true);
        if ( empty($miw_html_blurb_before) )
            $miw_html_blurb_before  = '';
               $form_fields['miw_html_blurb_before'] = array(
            'value' => $miw_html_blurb_before,
            'label' => __('HTML Blurb BEFORE image'),
                        'helps' => __('*Added by the MIW Plugin'),
                        'input'      => 'textarea'
        );
                $miw_html_blurb_after = get_post_meta($post->ID, '_miw_html_blurb_after', true);
        if ( empty($miw_html_blurb_after) )
            $miw_html_blurb_after  = '';
               $form_fields['miw_html_blurb_after'] = array(
            'value' => $miw_html_blurb_after,
            'label' => __('*Added by the MIW Plugin: HTML Blurb AFTER image'),
                        'helps' => __('*Added by the MIW Plugin'),
                        'input'      => 'textarea'
        );
        }
    return $form_fields;
}
        function renderPlaceInputFields( $post, $checked = '' ) {
            mythicalImageWarsDebug::logTrace();
            if ( empty($checked) )
        $checked = get_post_meta($post->ID, '_miw_place', true);
            // Yes this is hard-coded to 3 places only...however it's easy to add more here
            $places = array('none' => __('None'), 'first' => __('First'), 'second' => __('Second'), 'third' => __('Third'));
            if ( !array_key_exists( (string) $checked, $places ) )
        $checked = 'none';

            $out = array();
            foreach ( $places as $name => $label ) {
        $name = esc_attr($name);
        $out[] = "<input type='radio' name='attachments[{$post->ID}][miw_place]' id='miw-place-{$name}-{$post->ID}' value='$name'".
            ( $checked == $name ? " checked='checked'" : "" ) .
            " /><label for='miw-place-{$name}-{$post->ID}' class='align miw-place-{$name}-label'>$label</label>";
            } // End For loop for place rendering
            return join("\n", $out);
            }
        function renderEntryNumberColumnLabel ($columns, $detached) {
             mythicalImageWarsDebug::logTrace();
            $columns['miw_entry_number'] = _x('MIW Entry','column_name');
        return $columns;
        }
        function renderEntryNumberColumnCss () {
             mythicalImageWarsDebug::logTrace();
            echo '<style type="text/css">.fixed .column-miw_entry_number {width: 6em;} </style>'."\n";
        }

        function renderEntryNumberColumn ($columnName, $itemId) {
             mythicalImageWarsDebug::logTrace();
        if($columnName == 'miw_entry_number')
           echo get_post_meta($itemId, '_miw_entry_number', true);
    }

        function settingPageInit () {
            mythicalImageWarsDebug::logTrace();
            register_setting('main_section','main_section');
            add_settings_section(
                    'main_section',
                    'Main Section',
                    array('mythicalImageWars','renderSectionMain'),
                    'main'
                    );
            add_settings_field(
                    'main_first_place_image_url',
                    'First Place Image URL:',
                    array('mythicalImageWars','renderFileUpload'),
                    'main',
                    'main_section',
                    array('id' => 'first_place_image_url','description' => 'Image that appears as a overlay to the image flagged as first place')
                    );
            
            add_settings_field(
                    'main_second_place_image_url',
                    'Second Place Image URL:',
                    array('mythicalImageWars','renderFileUpload'),
                    'main',
                    'main_section',
                    array('id' => 'second_place_image_url','description' => 'Image that appears as a overlay to the image flagged as second place')
                    );
            add_settings_field(
                    'main_third_place_image_url',
                    'Third Place Image URL:',
                    array('mythicalImageWars','renderFileUpload'),
                    'main',
                    'main_section',
                    array('id' => 'third_place_image_url','description' => 'Image that appears as a overlay to the image flagged as third place')
                    );
            register_setting('debug_section','debug_section');
            add_settings_section(
                    'debug_section',
                    'Debug Section',
                    array('mythicalImageWars','renderSectionDebug'),
                    'debug'
                    );
            add_settings_field(
                    'debug_mode_toggle',
                    'Debug mode on?',
                    array('mythicalImageWars','renderCheckbox'),
                    'debug',
                    'debug_section',
                    array('id' => 'mode_toggle','section' => 'debug', 'description' => 'Image that appears as a overlay to the image flagged as third place')
                    );
           
        }
        public static function createAdminMenuItem () {
            mythicalImageWarsDebug::logTrace();
            add_options_page('Mythic Image Wars Settings','Mythic Image Wars Settings','manage_options','miw-settings-page', array('mythicalImageWars','renderSettingsPage'));
        }
        public static function renderSettingsPage () { 
            mythicalImageWarsDebug::logTrace();
            include( WP_PLUGIN_DIR .'/mythical-image-wars/templates/settingpage.php');
        }
        public static function renderSectionMain() {
             mythicalImageWarsDebug::logTrace();
                    echo '<p>Intro text for our settings section</p>';
                    echo '<div><p>Note: <i>Choose "Insert into Post" in the Media Uploader window to fill the url textbox.  After that you still need to "Save Settings" on the form to commit the image urls to the database.</i></p></div>';
}
        public static function renderSectionDebug() {
             mythicalImageWarsDebug::logTrace();
                    echo '<p>Intro text for our settings section</p>';
                    echo '<div><p>Note: <i>These settings add trace output to the bottom of the page, but only for admin and only traces within *this* plugin.</i></p></div>';
}
        public static function renderFileUpload($settings) {
             mythicalImageWarsDebug::logTrace();
            $options = get_option('main_section');
            echo '<input id="main_'.$settings['id'].'" class="miw-upload-text" type="text" size="96" name="main_section['.$settings['id'].']" value="'.$options[$settings['id']].'" />';
            echo '<a style="color:#333333;font-size:12px;text-decoration:none;vertical-align:bottom;" href="media-upload.php?type=image&amp;TB_iframe=true" onclick="return false;" title="Add Media" id="'.$settings['id'].'_button" class="thickbox add_media miw-upload-button" name="'.$settings['id'].'_button" >Upload/Insert <img width="15" height="15" src="'.admin_url().'/images/media-button.png?ver=20111005"></a>';
//            echo '<input id="'.$settings['id'].'_button" class="miw-upload-button" type="submit" name="'.$settings['id'].'_button" value="Upload Media" />';
            if ($settings['description'] != null )
                echo ('<div><span class="description">'.$settings['description'].'</span></div>');
        }
 
        public static function renderCheckbox($args) {
            mythicalImageWarsDebug::logTrace();
            $options = get_option('debug_section');
            echo '<input name="'.$args['section'].'_section['.$args['id'].']" id="'.$args['section'].'_'.$args['id'].'" type="radio" value="1" class="code" ' . checked( 1, $options[$args['id']], false ) . ' />True';
            echo '<input name="'.$args['section'].'_section['.$args['id'].']" id="'.$args['section'].'_'.$args['id'].'" type="radio" value="0" class="code" ' . checked( 0, $options[$args['id']], false ) . ' />False';
        }
        // END Admin screen logix
}
?>