# MyLabWebsite #

MyLabWebsite is a simple tool for creating and displaying the type of content used on the webpages for academic researchers, especially those in the sciences. It is based on the popular blogging software WordPress, and can be installed as a WordPress theme. However, rather than altering the appearance of a site, MyLabWebsite extends the functionality of WordPress to handle custom content for projects, protocols, and lab members. Additionally, publication lists, a standard feature of academic websites, can be generated automatically by searching third-party services such as PubMed or ORCiD. Also, altmetrics badges from [ImpactStory](http://impactstory.org/) can be appended to each publication to showcase the splash that paper has made in the community.

All this information is be entered on the WordPress Dashboard, and thus the site content can be customized without any coding. 

The style of the user-facing site is also part of the theme, however, we plan to add a choice of several different color schemes and font styles to the Dashboard as well. The theme is also extendable though the use of child themes. The standard MyLabWebsite theme is designed on a responsive (mobile-ready) layout.

## Installation and Setup Instructions ##

1. [Download and install WordPress on your web server](http://wordpress.org/download/). WordPress is free, and installation is designed to take less than 5 minutes.

1. From the WordPress home directory, navigate to wp-content/themes and create a new directory called MyLabWebsite

1. Download this repository and add the files to /wp-content/themes/MyLabWebsite

1. Open your browser and enter [your website URL]/wp-login.php.

1. Enter the username and password you created when installing WordPress.

1. At the bottom of the lefthand navigation, go to Settings > General. Make sure the Site Title and tagline are what you want them to be, since these will be client-facing.

1. Go to Appearance > Themes, select MyLabWebsite, and click Activate.

1. Go to Dashboard > MyLabWebsite settings. Decide on a style, and whether you will want a lab blog and/or a protocols page. Decide who will respond to emails sent using the contact form on the website, and enter their email address.

1. Enter identifying information for publications (for the whole lab) to be obtained from a third-party service. For PubMed, it helps to search by some combination of author name and affiliation information.

1. Decide if you would like to attach altmetric badges to the publications.

1. Enter some text for the site Footer. This is a good place to put links to the Department or University homepage.

1. Go to the Media tab. Upload any images you will want on the website. This includes large images for the homepage, pictures of lab members, and pictures to enhance various content (eg - Projects). <i>Note: While large TIFFs are appropriate for journal publications, page load times will be faster if pictures are converted to JPEG and kept no larger than they will appear to the user. As a guideline, the entire page is 760 pixels wide.</i>

1. Add images to the homepage slider. To do this, go to Media > Library, and select the image you want to add. In the MyLabSettings box on the right-hand side, enter any positive integer (the order can be used to determine the order in which images will appear in the slider). To remove the image from the slider, enter 0.

1. To add a lab member: Go to Users > Add New. Enter some information for that user, including a password, and click Add User. The lab member can now enter the WordPress dashboard and insert their biographical and publication information, and select their picture from the media library. <i>Note: New users are given the role of Contributer, and can change their information, and submit blog posts, but not change other aspects of the site. To give this lab members the ability to help manage the site, give him or her a higher role, such as editor.</i>

1. Managing projects, protocols (if desired), and blog posts (if desired) is fairly straightforward, and uses the WordPress WYSIWYG (what you see is what you get) editor.

1. Similarly, you can add some information to the Publications or Contact page (such as the address and phone number for the lab). The contact form and publications list will be generated automatically, but other information which will appear above this can be entered in the WYSIWYG editor.

1. That's it. Go to your website and click around. If something doesn't seem right, come back to the dashboard and make further changes, or if something goes really wrong, send me an email or report an issue on GitHub.

##Suggested Plugins##

MyLabWebsite is designed to stand alone and run without any additional plugins. If you plan to use a blog, however, it is recommended that you add a plugin to reduce comment spam. Some popular ones are Akismet and Disqus.

Direct links to social media can be helpful to get traffic to your site. I am personally a fan of AddAnywhere.

##Frequently asked questions##

<b>We can make a beautiful website using Dreamweaver/FrontPage. Why use WordPress?</b>

Editors such as Adobe Dreamweaver or Microsoft FrontPage can create beautiful sites without writing code, or perhaps someone in the lab knows some HTML and can write the site by hand. This is a perfectly good way to make a simple site. There are two places where such an approach can fall short: updating and responsive (mobile) design. Many lab websites do not get updated for many years, since even simple text editing requires making the changes on a desktop editor and then uploading the resulting documents. With MyLabWebsite, new publications appear automatically (eliminating the hassle of formatting), and other changes can be made in the WordPress editor.

Additionally, desktop editors are not able to create the responsive templates necessary to view the site on tablets or smart phones. With mobile browsing quickly overtaking desktop browsing within the next couple of years, responsive design is something everyone who has a website should at least consider.

<b>How can I change the appearance of the user-facing site?</b>

Beyond choosing from our list of standard styles, simple changes can be made just by altering the style.css document, by someone with a basic knowledge of Cascading Style Sheets. More complex changes may require the creation of a [child theme](http://codex.wordpress.org/Child_Themes), or altering the theme entirely. You are free to iterate in any way you choose, but we appreciate you providing a link back here. Letting us know if you create a new version is always appreciated.

<b>Why isn't this listed in the WordPress themes directory?</b>

Although extending the functionality of WordPress is a valid use for themes, the Themes Directory is specifically for themes which change the appearance of the basic WordPress blog format. Themes which create custom post types or fields (the heart of MyLabWebsite) are not allowed.

The publication list function of MyLabWebsite is also provided by ImpactPubs, which is listed in the WordPress plugins directory.

## To-do list ##

1. Internationalization (beginning with user-facing text)

1. Remove HTML-embedded Bootstrap classes and begin using LESS

1. Create alternate stylesheets (using LESS)