SimpleCMS
=========

What is SimpleCMS?
--------------
Glad you asked! SimpleCMS is a simple lightweight content management system using PHP and MySQL.

For updates check out the GitHub repository here:
https://github.com/JRogaishio/SimpleCMS

Or the creators account here:
https://github.com/JRogaishio

How do I setup SimpleCMS?
--------------
Once downloaded, place all files in your webservers root directory.
Then open up config.php and change the <code>DB_HOST</code>, <code>DB_USERNAME</code>,<code>DB_PASSWORD</code> and <code>SITE_ROOT</code> if necessary.

Also be sure to update the "RewriteBase" and "ErrorDocument" items on the .htaccess file to match your current setup.

Lastly, be sure that the <code>extension=php_openssl.dll</code> is not commented out on your php.ini installation.
<code>extension=php_openssl.dll</code> is required to make requests to GitHub for version checking and CMS updating.

Once that setup is complete, visit the admin.php page in your web browser and the database / tables will build themselves.


What can this new fangled CMS do?
--------------
SimpleCMS Includes the below features in no particular order:
- General website settings management
- Page management
-	Post management
-	Template management
-	User management

Cool, anything planned for the future?
--------------
You bet! Some planned features for SimpleCMS are:
- Plugin management and implementation
- User roles
- Messageboard plugins

License
--------------
(The MIT License)

Copyright (c) 2014 Jacob Rogaishio

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the 'Software'), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

