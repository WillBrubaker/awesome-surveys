=== Awesome Surveys ===
Contributors: WilltheWebMechanic, tobyhawkins, tofuSCHNITZEL
License: GPLv3.0+
Requires at least: 3.9.1
Tested up to: 4.2.2
Stable tag: 2.0.7
Tags: survey, form builder, survey form, data collection, feedback, free, plugin, polls, questionaire, poll builder, opinion, customer satisfaction
Donate Link: http://www.willthewebmechanic.com/awesome-surveys/

Create & publish feature-rich surveys with a few mouse clicks. All data collected remains in your control. Works better than healthcare.gov!

== Description ==

= IMPORTANT =
**If you are using a version less than 1.6.1 you need to update NOW**

Version 2.0 is a major rewrite to address the issues of data loss that some users have been experiencing. Surveys are now custom post types and leverage the WordPress post editor. Individual responses are stored as post meta - each response is represented by one row in the post meta table for the survey.

This plugin allows you to create surveys with an easy-to-use form builder, publish surveys with a simple shortcode and view survey results in the admin backend. You maintain control of your data. Automatic form validation is included.

= Features =

1. **You maintain ownership of your data** - does not rely on third-party services.
2. Allows the creation of any number of surveys to collect data from visitors to your site.
3. Build your survey forms with the powerful built-in form builder.
4. Optionally require login or cookie authentication in order for a user to take the survey.
5. Publish your surveys on pages or posts by including a shortcode **new** or link directly to them.
6. Advanced form field validation is included.
7. View results of your surveys in the admin area of your WordPress site.
8. Extendable through action/filter hooks.

= Known issues/limitations =

1. At this time, it is not possible to edit survey questions if the survey has responses. In the future I plan to re-implement this but only so much as to allow for the editing of text - You will not be able to add questions to a survey which has responses. You will not be able to add answers to questions if the survey has respones.

2. Since the surveys are now custom post types, the actual survey form is saved
as 'post_content'. This means that if WordPress was in English when you built the survey, but your site wants to be in another language, the English strings will still be output, i.e. 'Submit Response' & 'make a selection...'. Likewise, even if your WordPress site was in your language but you didn't have the translations available, those strings will be output in English. As a workaround, you can append some parameters to your url e.g. when on the general survey options screen, your url would look like this: "wp-admin/admin.php?page=awesome-surveys.php" add this to the url: "&translate-surveys=true" to force translation of all of your surveys. The translation strings for your language must be present in order for this to work. As another alternative, I would suggest implementing the WordPress '`the_content`' filter to change the strings to whatever you need them to be.

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

= How can I edit a survey? =

* Through the WordPress post editor for post type "awesome-surveys"

= How do I delete a survey? =

* Surveys are custom post types and deleting is done the same way you delete a post of page

= How do I create a survey? =

* This can be done by using the powerful survey form builder in the post editor for post type awesome survey

= How do I publish a survey? =

* Navigate to WTWM Plugins -> New Survey and build your survey. Once done, press the publish button. You can also embed your surveys via a simple shortcode that is displayed in the survey builder

= Is there a howto video? =

* There was one for an earlier version - this is not current, but the functionality is similar.

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
= v2.0.7 =
1. Duplicate survey functionality was retaining the survey id of the survey that it was duplicated from resulting in less than desirable results. This has been fixed by updating the post content to ensure that the new old survey id is replaced with the new survey id in the form field

= v2.0.6 =
1. implement/change escaping function used when echoing values into survey builder form
2. fire the auth method update action prior to the 'responses saved' action

= v2.0.5 =
1. Don't process survey response if survey is not published
2. Don't save post meta "existing_elements" if json_decode of same fails
3. Block user interface when adding survey questions
4. Menu position has more precision in an attempt to avoid clashes

= v2.0.4 =
1. Survey builder is now blocked by default and unblocked by JavaScript when loaded - this is an attempt to mitigate the "...bad array..." messages that are being reported

= v2.0.3 =
1. fix for undefined offset when deciding whether to send email
2. decode html entities in outgoing emails
3. answer metrics have returned - thanks to github user naimkhalifa
4. adds a 'countdown' option to textareas
5. bugfix for 'duplicate not working'
6. bugfix for 'required' textarea was breaking the form editor
7. results screen pagination added
8. fixed some non-translatable strings
9. more information added to the results bar graphs
10. CSS color tweaks to the results bar graphs

= v2.0.2 =
1. yet another bugfix

= v2.0.1 =
1. bugfix

= v2.0 - Yemaya =
1. Major rewrite
2. Surveys are now custom post types
3. responses are each stored in post meta individually
4. backward compatible to versions < 2.0, i.e. you can migrate older surveys to
the new version and you old shortcodes will still work - authentication methods map back to the old survey ids

= v1.6.3 =
1. Attempts mitigation of data loss
2. Updated jQuery ui css

= v1.6.2 =
1. bug fix - survey forms with special characters not properly json_decoded resulting in process response giving a bad request message - dev time 1 hour

= v1.6.1 =
1. **IMPORTANT** Fixes a security vulernability. UPDATE NOW!!

= v1.6 - La Barqueta =
1. *New Feature* Send emails on completion of surveys - 6 hours dev time, 0 hours of testing.
2. *Bug Fix* JavaScript error when editing a survey question/answer and no changes made - 15 minutes dev/testing time
3. Added `wwm_as_admin_script_vars` filter, through which one can change the number of answers available to questions with options (radio, checkbox, select) 15 minutes dev/testing time
4. Danish translations provided by WordPress user heinohund

= v1.5.2 =
1. **Important** If you use the Awesome Surveys CSV Exporter, please either update it to version 0.4 or de-activate it before updating Awesome Surveys.
2. Fixes for results by user
3. Enhancement - now requires confirmation when pressing 'reset survey'
4. Now uses WordPress constant SCRIPT_DEBUG for dev/workflow purposes

= v1.5.1 =
1. Changes how 'results by user' works

= v1.5 - Las Lajas =
1. Adds 'view results by user' functionality
2. German translations added
3. Whoops...forgot to load textdomain for translations
4. In an earlier version, I was using a closure or anonymous function - Earlier versions of PHP don't support such nonsense so that didn't work. Added a function which always returned true without the knowledge of some WordPress functions that accomplish this very thing. This has been updated with that.
5. Lots of work done to improve loading of translations and jQuery validation messages since WordPress now uses a setting for 'WPLANG' and the constant is no longer used.
6. Front end French translations started

= v1.4.4 =
1. Improved handling of non-English characters in the 'Survey Results' tab.

= v1.4.3 =
1. Bug fix - after re-ordering of questions, the 'edit question' & 'delete question' buttons would act on the wrong question. Thanks wordpress.org user vexweb for pointing this out.
2. Following form submission the browswer window should scroll back to the top of the form - see this thread: https://wordpress.org/support/topic/scroll-back-to-top-on-submit
3. Dutch translations added. Thanks wordpress.org user Mariavd for the translations

= v1.4.2 =
1. Added frontend check to ensure that JavaScript validation messages file exists before trying to load it (defaults to English) - Shout out to Kirk! Thanks for the report.

= v1.4.1 =
1. bug fix

= v1.4 =
1. Added drag & drop re-ordering of questions. See [this video](https://www.youtube.com/watch?v=-rZENBxYuOo) for a quick demonstration.
2. Metaboxes for news and ratings

= v1.3 =
1. Attempts to add localized form validation messages if WPLANG is defined and the messages file exists for WPLANG

= v1.2.2 =
1. admin javascript didn't update properly - fixed

= v1.2.1 =
1. Bug fix - 'Edit Survey' was actually cloning survey in certain cases.


= v1.2 =
1. New feature: Editing of questions/answers now available during the survey build process
2. New feature: Clone a survey
3. New feature: If a survey has no responses, it can be loaded into the survey builder and edited - questions can be added, answers can be added to questions
4. frontend js/css now gets a version string appended
5. frontend form is now rendered with the submit button disabled, it is then enabled by the included javascript. This is an attempt to alleviate the 404 errors that some users are experiencing - probably due to the javascript not loading for any number of reasons.
6. There was no good reason to require survey names to be unique, will now allow duplicate names.

= v1.1.2 =
1. New feature: conditionally edit survey authentication method
2. New feature: edit the thank you method
3. Added ability to redirect after survey submission (requires an addon)
4. Attempts to provide meaningful feedback if survey fails to save
5. Added uninstall.php
6. Bug fixes
7. Changed behaviour of the admin notice - can now be dismissed on a per-user basis
8. Code cleanup of external library

= v1.1.1 =
1. Fixed a bug where if two surveys were present on a page/post only the first one could be submitted
2. Further improvments to error handling
3. Further clean up of unneeded parts of the included PFBC package
4. The survey form relies heavily on javascript. If javascript is not available, the form submit button is disabled
5. Cleaner method of including js/css on admin pages implemented
6. Added a filter for the admin panel tabs/content - This can now be extended
7. Updated the jQuery validation plugin to stable release v1.13.0

= v1.1 =
1. Editing of Survey Name, Questions and Options/Answers now available by clicking a link
2. Surveys can now be deleted
3. Option to not load included CSS has been added
4. Removed heaps of unnecessary files from the included PFBC package
5. Bug fix in call to wp_list_pluck (invalid argument supplied for foreach)

= v1.0.3 =
1. Addresses the report of the "spinner" failing to go away in certain instances when the survey form is submitted
2. Attempts to provide something resembling useful feedback if the survey submission AJAX request fails

= v1.0.2 =
1. Bug fix on frontend output.
2. Bug fix for bad 'maxlength' validation values on text boxes and textareas.
3. This bug fix wouldn't have been possible without your bug reports. The users of this plugin ROCK!
4. Even with the bugs, it worked better than healthcare.gov

= v1.0.1 =
1. minor frontend styling changes

= v1.0 =
1. Initial Public Release

== Upgrade Notice ==

= v1.6.1 =
Addresses a potentially harmful vulnerability. Versions prior to 1.6.1 should not be used.

= v1.5.2 =
If you have the companion CSV export installed and it's at verson 0.3 or earlier, de-activate it prior to updating this plugin or update the exporter plugin to 0.4

= v1.0.2 =
fixes a couple of bugs. If you have been experiencing PHP Warnings where the shortcode was or if you've been having problems with text boxes/textareas, this update fixes those things.

== Additional Information ==

= Known Issues: =

* Versions prior to 1.6.1 are potentially vulnerable to malicious attacks - please do not use versions prior to 1.6.1

* The Async JS and CSS plugin can cause the javascript for Awesome Surveys to not be loaded. See this [support thread](http://wordpress.org/support/topic/404-error-when-submitting?replies=20) for a workaround.
* Survey form display hasn't likely been tested with your theme. Styling may break and the form may look horrible. If you experience this, please do contact me with suggestions on how to fix it for your particular theme.
* Cookie authentication for allowing/denying users to take the survey is very easily circumvented. If using cookie based authentication for your surveys, please keep this in mind. You will be far less likely to see "ballot-stuffing" if you are allowing registrations on your site and require the user to login to take the survey.
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
* **Your feature request here.**

= Credits =
* **Your name could be here** make a contribution today! (see the FAQ for suggestions of how you can help with the development)
