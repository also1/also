<?php

defined( 'ABSPATH' ) || exit;

$args = array(
  'avatar_size' => 45,
  'reply_text' => 'Ответить',
  'callback' => 'mytheme_comment',
  'style' => 'div',
); 
?>

<div class="comments">
  <div class="comments-title"><?php comments_number(); ?></div>
	<?php wp_list_comments($args); ?>
</div>

<?php if (!comments_open()){?>
	<div class="nocomments">Комментарии отключены.</div>
<?php }else{ ?>
		<?php if (!get_comments_number()) :?>
			<div class="nocomments">Комментариев пока нет, будьте первым.</div>
		<?php endif;?>
<?php } ?>

<?php
$fields =  array(
	'author' => '<div id="author-data">' . '<label class="author-label" for="author">' . __( 'Name' ) . '</label> ' . ( $req ? '<div class="required">*</div>' : '' ) . '<input class="w-input author-input" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' />',
	'email'  => '<label class="email-label" for="email">' . __( 'Email' ) . '</label> ' . ( $req ? '<div class="required">*</div>' : '' ) . '<input class="w-input email-input" id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' />',	'url'    => '</div>',
);

$comments_args = array(
		'fields' => $fields,
    'comment_notes_after' => '',
    'comment_notes_before' => '',
    'title_reply' => 'Добавить комментарий',
    'label_submit' => 'Отправить комментарий',
    'class_submit' => 'w-button comment-button',
		'comment_field' => '<label class="comment-label" for="comment">' . _x( 'Comment', 'noun' ) . '</label><textarea class="w-input comment-area" id="comment" name="comment" cols="" rows="8" aria-required="true"></textarea>'
);

comment_form($comments_args);

function mytheme_comment($comment, $args, $depth)
{
  $GLOBALS['comment'] = $comment;
  switch ( $comment->comment_type ) :
    case '' :
?>
     <div <?php if($GLOBALS['comment_depth'] !== 1){ echo comment_class('children', '', '', false); } else { echo comment_class('', '', '', false); } ?> id="comment-<?php comment_ID() ?>" >
      <div class="comment-body" id="comment-<?php comment_ID(); ?>">
        <?php echo get_avatar( $comment->comment_author_email, $args['avatar_size']); ?>
        <div class="comment-author"><?php printf(__('%s'), get_comment_author_link()) ?></div>
        <a class="comment-date" href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ) ?>"><?php printf(__('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a>
        <?php if ($comment->comment_approved == '0') : ?><?php _e('Your comment is awaiting moderation.') ?><?php endif; ?>
        <div class="comment-text"><?php comment_text() ?></div>
        <?php edit_comment_link( __( 'Изменить' ), ' ' ); ?>
        <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
      </div>
<?php
    break;
    case 'pingback'  :
    case 'trackback' :
?>
      <div class="post pingback">
        <?php comment_author_link(); ?>
        <?php edit_comment_link( __( 'Изменить' ), ' ' ); ?>
<?php
    break;
  endswitch;
}

?>