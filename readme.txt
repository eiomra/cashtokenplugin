# cashtokenplugin

Download and install the plugin
This plugin uses OAuth 2.0 authentication to generate bearer token, 
the callback url is http://localhost:3000/oauth-callback
So we need to configure wordpress .htaccess

# Update your .htaccess
Goto your .htaccess 
Under RewriteBase /
add these codes

RewriteCond %{THE_REQUEST} /oauth-callback\.php\?code=([^\s&]+) [NC]
RewriteRule ^ /oauth-callback?code=%1 [R=301,L]
RewriteRule ^oauth-callback$ /oauth-callback.php [L]

Then add these codes after </IfModule>

<FilesMatch "\.(php)$">
    SetHandler application/x-httpd-php
</FilesMatch>


To run this project on your machine, your server needs to be http://localhost:3000
because the redirect uri is already preconfigured to that. 

To open this project with a port link like `http://localhost:3000/` in XAMPP localhost, you need to follow these steps: 

Open the `httpd.conf` file located in the `conf` directory of your XAMPP installation. For example, on Windows, you can find it at `C:\xampp\apache\conf\httpd.conf`.

   - Look for the line that says `Listen 80` and change it to `Listen 3000`.

   - Additionally, find the line that says `ServerName localhost:80` and change it to `ServerName localhost:3000`.

   - Save the changes to the `httpd.conf` file and close it.

Now, start the Apache server from the XAMPP control panel.

Your Apache is running on port 3000, open your web browser and enter the following URL: `http://localhost:3000/`

after successful installation, goto the plugin settings at the wordpress admin menu and update the url you want users to
be redirected to after they have successfully generated their token.

Use this shortcode to display all posts [cashtoken_posts]

Happy Building!
