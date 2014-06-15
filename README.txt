=== Awesome Surveys ===
Contributors: WilltheWebMechanic
License: GPLv3.0+
Requires at least: 3.9.1
Tested up to: 3.9.1
Stable tag: 1.0.1
Tags: survey, form builder, survey form, data collection, feedback, free, plugin, polls, questionaire, poll builder, opinion, customer satisfaction
Donate Link: http://www.willthewebmechanic.com/awesome-surveys/

Create & publish feature-rich surveys with a few mouse clicks. All data collected remains in your control. Works better than healthcare.gov!

== Description ==

This plugin allows you to create surveys with an easy-to-use form builder, publish surveys with a simple shortcode and view survey results in the admin backend. You maintain control of your data. Automatic form validation is included.

= Features =

1. **You maintain ownership of your data** - does not rely on third-party services.
2. Allows the creation of any number of surveys to collect data from visitors to your site.
3. Build your survey forms with the powerful built-in form builder.
4. Optionally require login or cookie authentication in order for a user to take the survey.
5. Publish your surveys on pages or posts by including a shortcode.
6. Advanced form field validation is included.
7. View results of your surveys in the admin area of your WordPress site.
8. Contextual help available for many of the survey builder options
9. Extendable through action/filter hooks.

== Installation ==

= Option 1 =

* Within your WordPress admin area, navigate to "Plugins".
* Click the "Add New" link near the top of your screen.
* Click the "Upload" link.
* Click the browse button and navigate to wherever you downloaded the zip file to, select the zip file
* Click the "Install Now" button
* Click "Activate Plugin"

= Option 2 =

* Extract the zip file
* Upload (ftp) the resulting `awesome-survyes` folder to your `/wp-content/plugins/` directory.
* Activate "Awesome Surveys" through the "Plugins" menu in WordPress

= After Installation: =

* Once activated, your admin menu will have an item labeld "WtWM Plugins", that item has a submenu item called "Awesome Survyes", this is where you can configure build your surveys & view their results.
* Rejoice in how amazingly easy it is to create and publish surveys.

== Frequently Asked Questions ==

= How do I create a survey? =

* This can be done by using the powerful survey form builder located in the plugin configuration screen.

= How do I publish a survey? =

* Surveys can be published in your blog posts or pages using a simple shortcode. After you have built a survey, its details are available in the plugin configuration screen under the 'Your Surveys' tab. The shortcode for each of your surveys is displayed there.

= Is there a howto video? =

* Yes!

[youtube https://www.youtube.com/watch?v=YIta2rDE-QU]

= How can I support the development of this plugin? =

= The most obvious way is to =
[donate](http://www.willthewebmechanic.com/awesome-surveys/ "Support Future Development").
= However, there are many other ways that you can contribute. =
* By simply [rating](http://wordpress.org/support/view/plugin-reviews/awesome-surveys "Review this plugin") this plugin you provide me valuable feedback on what is important to the users of this plugin.
* If you find a bug, report it in the [support forums](http://wordpress.org/support/plugin/awesome-surveys "Get Support").
* If you would like to see more features, [let me know](http://wordpress.org/support/plugin/awesome-surveys "Feature Request").
* Can you provide artwork for the banner or other assets? Please do - I'll put your name in pixels and will be forever grateful.
* Are you a developer and would like to contribute code to this plugin? Find me on [github](https://github.com/WillBrubaker/awesome-surveys "Fork Me") and send a pull request (which will also result in your name in pixels).
* [Tweet](http://ctt.ec/qNg6L "Shout it From the Rooftops") about this plugin, write about it on your blog.
* Create translations for this plugin, provide improved help documentation, create a 'how-to-use' video.
* Any of the above actions are truly and greatly appreciated!


== Screenshots ==

1. The form builder
2. Survey form built, ready to save
3. Survey form output on the frontend
4. The thank you message displayed after the survey has been submitted
5. Survey results view


== Changelog ==

= v1.0.1 =
1. minor frontend styling changes

= v1.0 =
1. Initial Public Release

== Upgrade Notice ==

= v1.0.1 =
* This update does very little other than change some styling. If you're happy with the way survey forms appear, there is no need to update at this time.

== Additional Information ==

= Known Issues: =

* Survey form display hasn't likely been tested with your theme. Styling may break and the form may look horrible. If you experience this, please do contact me with suggestions on how to fix it for your particular theme.
* Cookie authentication for allowing/denying users to take the survey is very easily circumvented. If using cookie based authentication for your surveys, please keep this in mind. You will be far less likely to see "ballot-stuffing" if you are allowing registrations on your site and require the user to login to take the survey.
* This plugin is using a pre-release version of the jQuery validation plugin which fixes a bug related to validating input type="number". Other issues in this pre-release may be present.
* Many developer features are unfinished at this time. If you are developing an extension for this plugin or using any of the action/filter hooks included, proceed with caution.

= Included Software =

* Uses the [PHP Form Builder class](https://code.google.com/p/php-form-builder-class/ "PFBC") which is GPL v3 licensed.
* Uses the [jQuery validaiton plugin](http://jqueryvalidation.org "jQuery validation") which is MIT licensed.
* Uses [pure css](http://purecss.io) which is licensed under the Yahoo! BSD license
* Uses [normalize css](https://github.com/necolas/normalize.css) which is MIT licensed

= To Do List =

* Widget to display surveys?
* Ability to add classes to survey form elements and add custom css.
* Ability to export .csv file of the surveys
* Add some survey management options (e.g. Delete survey, rename survey...)
* View survey results sorted by how each individual respondent answered questions.
* **Your feature request here.**

= Credits =
* **Your name could be here** make a contribution today! (see the FAQ for suggestions of how you can help with the development)