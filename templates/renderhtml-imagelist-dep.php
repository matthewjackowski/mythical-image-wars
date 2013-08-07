<?php 
/* This is the default template that renders the Mythical Image War Image List/Contest/Whatever
 * Feel free to copypasta this to make your own...or just hack at it directly the beasts don't care
 * Template constructs which you should not touch are lablled 'DO NOT TOUCH'
 * Also here's a reference for the data fields from the MIW plugin which can be used inside of the loop
   'title' => This is the 'title' field for the image
   'entry_number' => This is a MIW custom field, it's the entry number of the image...it should be in sequencial order of posting
   'entry_number_padded' => Same as above but we add some padding zeros to help with filenames (ie "017", "005")
   'html_blurb_before' => This allows you to cram some html BEFORE displaying the image...witty copy goes here
   'html_blurb_after' => This allows you to cram some html AFTER displaying the image...boring copy goes here
   'place_image_src' => This is the url to the "place" image (ie first, second, third) that will overlay on the main image
   'place_image_css' => This sets an inline style attribute that allows for styling the overlay place image
   'original_src' => This is the url to the uploaded image
   'original_width' => This is the width of the uploaded image
   'original_height' => This is the height of the uploaded image
 * Ok from here it gets boring since we repeat for *every* rendition...if you customized your auto sizes, it should be reflected in this data
 * I'm just going to list the specific key names to allow for copypasta
   'thumbnail_src' 'thumbnail_width' 'thumbnail_height'
   'medium_src' 'medium_width' 'medium_height'
   'post_thumbnail_src' 'post_thumbnail_width' 'post_thumbnail_height'
   'large_feature_src' 'large_feature_width' 'large_feature_height'
   'small_feature_src' 'small_feature_width' 'small_feature_height'
 * The next section is experimental exif keys...its not guaranteed to work in any way  
   'exif_aperture' 'exif_credit' 'exif_camera' 'exif_caption' 'exif_created_timestamp' 'exif_copyright' 'exif_focal_length' 'exif_iso' 'exif_shutter_speed' 'exif_title'
 * Last word...you *must* use these keys in the loop with the "$miwImage" array...oh and don't forget to "echo" it
 */
?>
<?php
foreach ($miwImages as $miwImage) { // DO NOT TOUCH
?>
<p>&nbsp;</p>
<p><?php echo $miwImage['html_blurb_before'];?></p>
<p>&nbsp;</p>
<p><a title="Mythical Beast Wars - <?php echo $miwImage['title'];?>" rel="lightbox" href="<?php echo $miwImage['original_src'];?>" style="border: 0px none;">
        <span style="<?php echo $miwImage['place_image_css'] ?>background-image: url('<?php echo $miwImage['place_image_src'];?>');"></span>
        <img style="padding:0px;border: 4px double #000; outline: 1px solid #000;outline-offset: -2px;" width="<?php echo $miwImage['medium_width'];?>" height="<?php echo $miwImage['medium_height'];?>" alt="Mythical Beast Wars - <?php echo $miwImage['title'];?>" src="<?php echo $miwImage['medium_src'];?>" title="Mythical Beast Wars - <?php echo $miwImage['title'];?>" class="alignnone size-full wp-image-699"/>
    </a></p>
<p><img width="115" height="75" alt="" src="http://mythicalbeastwars.com/wp-content/uploads/2012/02/number<?php echo $miwImage['entry_number_padded'];?>.jpg" title="number<?php echo $miwImage['entry_number'];?>" style="margin-left: 10px; margin-right: 10px;" class="alignleft size-full"/>
<?php echo $miwImage['html_blurb_after'];?>
<p><strong><br>
</strong></p>
<p><?php //echo self::renderVotingForm(array('postid' => $miwImage['id'])); ?></p>
<p></p>
<p>&nbsp;</p>
<?php
} // End of for loop DO NOT TOUCH
?>
