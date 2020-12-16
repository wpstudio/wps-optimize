# WPS-Optimize
This class provides a wrapper to optimize WordPress and remove unnecessary or unwanted functions and scripts.

## How to use in your project
You will need to add the wps-optimize.php file to your theme, plugin, or child theme. Then require the file from your functions.php file or autoload it.

## Optimization Options
You have the ability to choose the optimizations for your WordPress site inside an array of optimizations.

Below is the code fo the default optimizations:

```
$optimisations = array(
  'blockExternalHTTP'         => false, // Block requests to external http on the front-end side. Thus, blocks all request that are done by plugins to external addresses.
  'deferCSS'                  => false, // Adds defer="defer" to all enqueued JavaScript files.
  'deferJS'                   => true,  // Defers all registered scripts using the loadCSS function from the Filament Group.  
  'disableComments'           => false, // Disables the comments functionality and removes it from the admin menu.
  'disableEmbed'              => false, // Removes the script files that are enqueued by the WordPress media embed system.
  'disableEmoji'              => true,  // Removes the scripts that are enqueued for displaying emojis.
  'disableFeeds'              => false, // Removes the post feeds.
  'disableHeartbeat'          => false, // Unregisters the heartbeat scripts, which is usually responsible for autosaves.
  'disablejQuery'             => false, // Removes the default jQuery script.
  'disablejQueryMigrate'      => true,  // Removes the jQuery Migrate script.
  'disableRestApi'            => false, // Disables the rest api.
  'disableRSD'                => true,  // Removes the RDS link in the head section of the site.
  'disableShortlinks'         => true,  // Removes the shortlinks in the head section of the site.                     
  'disableVersionNumbers'     => true,  // Removes the version trail in enqueued scripts and styles.           
  'disableWLWManifest'        => true,  // Removes the WLW Manifest links in the head section of the site.
  'disableWPVersion'          => true,  // Removes the WP version from the head section of the site.           
  'disableXMLRPC'             => true,  // Disables the xmlrpc functionality.
  'jqueryToFooter'            => true,  // Moves the default jQuery script to the footer.
  'limitCommentsJS'           => true,  // Limits the JS for comments only to singular entities
  'limitRevisions'            => true,  // Limits the number of revisions to 5
  'removeCommentsStyle'       => true,  // Removes the .recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;} styling in the head section
  'slowHeartbeat'             => true,  // Slows the heartbeat down to one per minute
  'disableGutenpoop'          => true   // Disable the Gutenberg/Block editor for the post edit screen
);
```

## Create Instance
Once you specify the $optimizations in the array above, you can create a new instance of the WPS_Optimize class to run the optimization.

```
$optimize = new WPStudioCode\WPS_Optimize\WPSOptimize($optimisations);
```

## Future Function Additions
- Disable Updates  (Choose to disable Everything or Core/Minor/Major, Plugins, Themes)
- Hide the "Thank you for creating with WordPress" footer
- Hide the "Screen Options" selection drop down
- Hide the "Help" selection drop down
- Hide all "Nag" Messages from the Admin Dashboard
- Hide "Translation Update Required" messages from the Admin Dashboard
- Hide the "Theme Update Required" messages from the Admin Dashboard
- Hide the "Plugin Update Required" messages from the Admin Dashboard
- Hide the "WordPress Core Update Required" messages from the Admin Dashboard
