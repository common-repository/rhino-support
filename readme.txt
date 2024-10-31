=== Rhino Support for WordPress ===
Contributors: rhinosupport
Donate link:
Tags: Support Desk, Customer Support, Rhino Support, Help Desk, Help Desk Software, Support Ticket
Requires at least: 3.3
Tested up to: 3.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily connect a WordPress site to your help desk, powered by RhinoSupport.com. Instantly embed support ticket forms with a simple shortcode.

== Description ==

Rhino Support for WordPress allows you to bring your RhinoSupport.com help desk right into any site powered by WordPress.

Visitors can create new support tickets as well as view existing tickets (if logged in) - without even leaving your WordPress site.

Here's a quick overview:

**1) Various Support Options**

Within your WordPress site you can have a variety of support options for your visitors based on your preferences including:

Floating Support Tab - This floating support tab can be positioned wherever you like, added to any post or page (or every one if you like) and customized with any colour or text.

Ticket Form on Post/Page - With a simple short code you can add a ticket form to any post or page so your visitors can easily submit a new support ticket from within your site.

**2) Short Codes**

The Rhino Support short codes make it quick and easy for you to add a new ticket form or even list open tickets for any logged in users on any post or page.

From the visual editor, the short codes are just one click away.

**3) Create Different User Experiences**

Sometimes you may have the need to display different support options for people who are logged into your site versus regular visitors.

Commonly this is used for "presales" questions vs. actual customer related questions.

This can easily be done by selecting what departments you want to display for logged in users and what departments you want to display for everyone else.

Furthermore, if a person is logged into your WordPress site, you can quickly list all the support tickets for a particular user by dropping the Rhino Support "list" short code on any post or page. If the user is logged in and they view that page, they'll be able to see the status of all their existing tickets without even leaving your site.

Not a RhinoSupport.com customer? Get your own [help desk software](http://www.rhinosupport.com) account which includes unlimited agents and a generous 45-day free.

Twitter: [@RhinoSupport](http://twitter.com/rhinosupport)

== Installation ==

1. Upload `rhino-support` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


1. Install Rhino Support either via the WordPress.org plugin directory, or by uploading the `rhino-support` folder to the `/wp-content/plugins/` directory on your server.
2. After activating Rhino Support for WordPress, you will be asked to connect to Rhino Support via your API key. Follow the instructions inside the plugin for obtaining your Rhino Support API key.
3. You're all set. There are written instructions and training videos for using the plugin included.

== Frequently asked questions ==

= Will this turn my WordPress site into a support ticket system? =

No, this is used to make it convenient for visitors and users of your site to submit tickets through the Rhino Support system. You'll need to set up an account at Rhino Support first.

= Do I need a Rhino Support account to use the plugin? =

Yes, inside the plugin there is information on how to get a Free Trial to the Rhino Support Service.

== Screenshots ==

1. Easily connect the plugin to your Rhino Support account by simply pasting the API key that is issued to your account.

2. Visitors to your site can submit tickets to the departments that you configure. You can give access to certain departments for visitors and different departments for users that are logged in to your WordPress site.

3. Simply insert a shortcode on a page that will embed a form to submit a support ticket.

4. The form is displayed automatically when someone visits the page that you created. It will already include their name and email if they are logged in to your site.

5. You can also create a tab to display a "Contact Us" option on the side of your site. You have the options to select it on particular pages and posts or simply show it throughout your entire site.

6. You can create a page with a shortcode that will list the tickets of the users that are logged in to your site.

7. Logged in users see a list of all their tickets in the Rhino Support System. Each ticket will have a link to go directly to that ticket on the Rhino Support System.


== Changelog ==

= 1.0.62 =
- Fixed table issues with listing tickets.

= 1.0.61 =
- Fixed issue with convert tickets not working on the first comment on the comments page.

= 1.0.60 =
- Fixed bug with private levels appearing on the list of departments on the create ticket section.

= 1.0.59 =
- Fixed attachments that appear on the ticket even though no attachment was uploaded.
- Added support to line breaks in ticket messages.

= 1.0.58 =
* Fixed shortcode inserter in tinymce issue with WP 3.9.

= 1.0.57 =
* Added file attachment support in create ticket form.

= 1.0.56 =
* Added feature with will sync the create ticket form with what the user have set on their Rhino Account.

= 1.0.53 =
* Fixed other javascript issues for select2 js, select2 file not being called.

= 1.0.52 =
* Fixed Javascript Errors on rhino admin settings
* Added scheduled api checking so we only check the license validity once a day.

= 1.0.51 =
* Fixed display issue on list ticket when last message has qoutations

= 1.0.48 =
* Added a See More pagination on list tickets shortcodes

= 1.0.47 =
* Change the number of tickets returned on the list ticket short code to 50
* Added the "Don Not Display" option on the scrolling tab section
* Fixed some issue with display form on listticket shortcode

= 1.0.45 =
* Added feature that lets the Admin Convert comments into tickets
* Added Notifications when updating Settings
* Fixed bug on scrolling tab settings only showing the latest 5 posts
* Updated the function on how to fetch the domain of the client's supportdesk

= 1.0.41 =
* Initial Public Release



== Upgrade notice ==

