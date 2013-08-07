<?php
 /* This is the default template that renders the Mythical Image War voting form...it really is just is a re-styled commentform
  * Feel free to copypasta this to make your own...or just hack at it directly the beasts don't care
  * Template constructs which you should not touch are lablled 'DO NOT TOUCH'
  * Rating style and images lifed from: http://www.komodomedia.com/blog/2006/01/css-star-rating-part-deux/
  */
?>
<script language="javascript" type="text/javascript">
function submitVote(voteValue) {
document.forms["votingForm"].elements["comment"].value="[votevalue:"+voteValue+"]";
document.forms["votingForm"].submit();
}
</script>

 <form action="<?php echo site_url( '/wp-comments-post.php' ); ?>" method="post" id="votingForm">
    <ul class="star-rating">
<li class="current-rating" style="width:60%;">Currently 3/5 Stars.</li>
<li><a href="javascript:void(0);" onclick="submitVote(1);return false;" title="1 star out of 5" class="one-star">1</a></li>
<li><a href="javascript:void(0);" onclick="submitVote(2);return false;" title="2 stars out of 5" class="two-stars">2</a></li>
<li><a href="javascript:void(0);" onclick="submitVote(3);return false;" title="3 stars out of 5" class="three-stars">3</a></li>
<li><a href="javascript:void(0);" onclick="submitVote(4);return false;" title="4 stars out of 5" class="four-stars">4</a></li>
<li><a href="javascript:void(0);" onclick="submitVote(5);return false;" title="5 stars out of 5" class="five-stars">5</a></li>
</ul>
<input type="hidden" id="comment" name="comment">		
<input name="comment_post_ID" value="<?php echo $postid?>" id="comment_post_ID" type="hidden">
<input name="comment_parent" id="comment_parent" value="0" type="hidden">
<?php do_action( 'comment_form', $post_id ); ?>
     </form>