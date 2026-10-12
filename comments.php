<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( post_password_required() ) return;

function noghre_comment_callback( $comment, $args, $depth ) {
	$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
	?>
	<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'noghre-comment' ); ?>>
		<div class="noghre-comment-inner">
			<div class="noghre-comment-avatar"><?php echo get_avatar( $comment, 56 ); ?></div>
			<div class="noghre-comment-body">
				<div class="noghre-comment-meta">
					<span class="noghre-comment-author"><?php comment_author(); ?></span>
					<span class="noghre-comment-date">
						<a href="<?php echo esc_url( get_comment_link( $comment ) ); ?>"><?php echo esc_html( noghre_jalali_date( get_comment_time( 'U' ), 'j F Y' ) ); ?></a>
					</span>
					<?php if ( '0' === $comment->comment_approved ) : ?>
						<span class="noghre-comment-pending">در انتظار تایید مدیر</span>
					<?php endif; ?>
				</div>
				<div class="noghre-comment-content">
					<?php comment_text(); ?>
				</div>
				<div class="noghre-comment-reply">
					<?php
					comment_reply_link( array_merge( $args, array(
						'reply_text' => 'پاسخ',
						'depth'      => $depth,
						'max_depth'  => $args['max_depth'],
					) ) );
					?>
				</div>
			</div>
		</div>
	<?php
}
?>
<?php
$comments_count = get_comments_number();
$toggle_label = $comments_count > 0
	? sprintf( 'نمایش %s دیدگاه', number_format_i18n( $comments_count ) )
	: 'ثبت دیدگاه';
?>
<div class="comments-area" id="comments">
	<button type="button" class="comments-toggle" aria-expanded="false" aria-controls="comments-drawer-panel">
		<?php echo noghre_icon( 'chevron' ); ?>
		<span><?php echo esc_html( $toggle_label ); ?></span>
	</button>

	<div class="comments-drawer-panel" id="comments-drawer-panel">
		<div class="comments-drawer-inner">
			<?php if ( have_comments() ) : ?>
				<h3 class="comments-title">
					<?php
					printf(
						'%s دیدگاه برای «%s»',
						number_format_i18n( get_comments_number() ),
						esc_html( get_the_title() )
					);
					?>
				</h3>

				<ol class="comment-list">
					<?php
					wp_list_comments( array(
						'style'    => 'ol',
						'callback' => 'noghre_comment_callback',
					) );
					?>
				</ol>

				<?php the_comments_navigation(); ?>
			<?php endif; ?>

			<?php if ( ! comments_open() && get_comments_number() ) : ?>
				<p class="noghre-comments-closed">امکان ثبت دیدگاه جدید بسته شده است.</p>
			<?php endif; ?>

			<?php
			comment_form( array(
				'title_reply'          => 'دیدگاه خود را بنویسید',
				'title_reply_to'       => 'پاسخ به %s',
				'comment_notes_before' => '',
				'comment_notes_after'  => '',
				'label_submit'         => 'ارسال دیدگاه',
				'class_submit'         => 'btn btn-primary',
				'comment_field'        => '<p class="comment-form-comment"><label for="comment">دیدگاه شما</label><textarea id="comment" name="comment" rows="5" required></textarea></p>',
				'fields'               => array(
					'author' => '<p class="comment-form-author"><label for="author">نام</label><input id="author" name="author" type="text" value="" required></p>',
					'email'  => '<p class="comment-form-email"><label for="email">ایمیل</label><input id="email" name="email" type="email" value="" required></p>',
				),
			) );
			?>
		</div>
	</div>
</div>
