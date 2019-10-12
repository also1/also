

<?php wp_footer(); ?>
<?php if(file_exists(dirname( __FILE__ ).'/js/front.js')){ ?><script type="text/javascript" src="<?php bloginfo("template_url"); ?>/js/front.js?ver=1570911575"></script><?php } ?>
<?php if(file_exists(dirname( __FILE__ ).'/js/shop.js')){ ?><script type="text/javascript" src="<?php bloginfo("template_url"); ?>/js/shop.js?ver=1570911575"></script><?php } ?>
<?php if(file_exists(dirname( __FILE__ ).'/js/main.js')){ ?><script type="text/javascript" src="<?php bloginfo("template_url"); ?>/js/main.js?ver=1570911575"></script><?php } ?>
<?php if(file_exists(dirname( __FILE__ ).'/mailer.php')){ include_once 'mailer.php'; } ?>
<?php if(file_exists(dirname( __FILE__ ).'/footer_code.php')){ include_once 'footer_code.php'; } ?>
<?php if(function_exists('the_field')) { the_field('footer_code', 'option'); } ?>
</body>

</html>