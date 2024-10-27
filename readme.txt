=== AdShare ===
Contributors: mtinsley
Donate link: http://tinsology.net/plugins/adshare/
Tags: ad, google, adsense, share, rotate, ads
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: 0.2

AdShare allows you to rotate adsense ads based on the author of the current post. See http://tinsology.net/plugins/adshare/
for usage.

== Description ==

AdShare allows you to rotate adsense ads based on the author of the current post. See http://tinsology.net/plugins/adshare/
for usage.

== Installation ==

1. Upload the adshare folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. The AdShare link will appear under your 'Settings' menu
1. Add your your default publisher id (ie pub-############).
1. Add any additional publisher ids.
1. For ads hardcoded into your theme, replace google_ad_client = "yourpubid"; with google_ad_client = "<?php global $pubid; echo $pubid; ?>";
1. For ads in widgets or posts, replace google_ad_client = "yourpubid"; with google_ad_client = "[pubid]";

== Frequently Asked Questions ==

= Where can I find my publisher ID =

Login to your adsense account, your ID should appear in the top right corner

= Where can I ask additional questions? =

Here: [AdShare at Tinsology.net](http://tinsology.net/plugins/adshare/)
