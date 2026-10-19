<?php //site configuration defaults
/* ====================================================================================================================== */
/* NoNonsense Forum v26 © Copyright (CC-BY) Kroc Camen 2010-2015
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/


/* --- copy this file to 'config.php' and customise to your liking,
       DON’T DELETE OR MODIFY 'config.default.php'! --- */


//set to true to display PHP errors in the browser (development only! a stack trace can expose server paths and
//internals). errors are always written to the server's error log, whether this is on or off
@define ('FORUM_DEBUG',         false);

//path, relative to this file, where the private data should be stored. e.g. "../users" to go above webroot.
//note that this is a server path, so use "/" on UNIX servers and "\" on Windows ones. don't provide an end slash.
//if you wish to run without HTAccess, you will need to change this to point to a private location
@define ('FORUM_USERS',         'users');

//forum’s title. used in theme, and in RSS feeds
//WARNING: changing this won’t update the index RSS feed containing this name; post/delete a thread to regenerate the
//         'index.xml' file so as to see the change
@define ('FORUM_NAME',          'NoNonsense Forum');

//timezone to use for all datetimes
//this must be a string from this list: <php.net/manual/en/timezones.php>, e.g. "Europe/London"
@define ('FORUM_TIMEZONE',      'UTC');

//the domain name (and optional port) the forum is served from, e.g. "forum.example.com" or "example.com:8080"
//it is used to build absolute URLs (RSS feeds, redirects, canonical links, cookies). if left unset, it is taken
//from the request's `Host` header, which is supplied by the visitor and can be forged (a forged value could end
//up in your RSS feed or redirects), so on a public forum it is recommended to uncomment the line below and pin it
#@define ('FORUM_HOST',         'forum.example.com');

//if the forum should use--and force--HTTPS connections and URLs:
//if set to true, RSS feeds will be saved with HTTPS URLs, and HTTP connections will automatically be redirected to HTTPS.
//"HSTS" <en.wikipedia.org/wiki/HTTP_Strict_Transport_Security> will be used to tell clients to use HTTPS by default
//NOTE: If you change this setting, old RSS feeds will still contain HTTP URLs, but this won’t pose a problem as the HSTS
//      header will tell browsers to automatically redirect these to HTTPS
@define ('FORUM_HTTPS',         false);

//folder name of the theme to use, in "/themes/*"
@define ('FORUM_THEME',         'greyscale');

//if posting is allowed. change to false and nobody will be able to post new threads or reply
@define ('FORUM_ENABLED',       true);
//if new users are allowed to register. set to false and only existing registered users will be allowed to post
@define ('FORUM_NEWBIES',       true);

//number of threads and posts to show per page
//WARNING: changing these will inadvertadely invalidate post permalinks, decide on these numbers in the beginning
@define ('FORUM_THREADS',       50);
@define ('FORUM_POSTS',         25);

//minimum number of seconds between two posts (new threads, replies and appends) from the same client, to slow down
//spam bots. set to 0 to disable. the client is identified by its IP address, which is stored hashed in 'flood.txt'
//(never in plain text); '.htaccess' blocks that file from being downloaded
@define ('FORUM_FLOOD',         30);

//maximum allowed size (number of characters) of input fields
@define ('SIZE_NAME',           20);            //user name
@define ('SIZE_PASS',           20);            //password
@define ('SIZE_TITLE',          100);           //post title
@define ('SIZE_TEXT',           50000);         //post message


?>