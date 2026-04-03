		<div id="footer">

			<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
				<ul class="widget-area">
					<?php dynamic_sidebar( 'footer-1' ); ?>
				</ul>
			<?php endif; ?>

			<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
				<ul class="widget-area">
					<?php dynamic_sidebar( 'footer-2' ); ?>
				</ul>
			<?php endif; ?>

			<?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
				<ul class="widget-area">
					<?php dynamic_sidebar( 'footer-3' ); ?>
				</ul>
			<?php endif; ?>

			<?php if ( is_active_sidebar( 'footer-4' ) ) : ?>
				<ul class="widget-area">
					<?php dynamic_sidebar( 'footer-4' ); ?>
				</ul>
			<?php endif; ?>

			<div class="clearfix"></div>
			
			<div style="padding: 30px 15px 15px; text-align: center;">
				This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy">Privacy Policy</a> and <a href="https://policies.google.com/terms">Terms of Service</a> apply.
			</div>
		</div>
<?php wp_footer(); ?>

<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '520340979105270');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=520340979105270&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->
</body>
</html>