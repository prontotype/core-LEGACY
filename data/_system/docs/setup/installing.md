# Installing Prontotype

To get up and running with Prontotype, follow the following steps:

1. Make sure you have [Composer installed on your system](http://getcomposer.org/doc/00-intro.md#globally).
1. Download the [latest version of Prontotype](https://github.com/prontotype/prontotype/archive/master.zip) from Github, unzip it and place the files in the web root of your server.
1. [Install Prontotype's dependencies](http://getcomposer.org/doc/00-intro.md#using-composer) by using Composer from the command line.
1. Make sure that the `/cache` directory has the correct permissions to be writeable by PHP (probably 777 or 755 depending on your server configuration).

Once that is all done, visiting your domain at `http://myprototype.com` (or whatever you are using!) should display the default Prontotype welcome page.