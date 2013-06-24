# Configuration

Each prototype can have it's own set of configuration files to override the (sensible!) defaults or to tweak the setup to allow for things like 'pretty' URLs, password-protection, custom URL routing and more. You can also add environment-specific configuration files too if you like!

All Prontotype configuration is done via [YAML](http://www.yaml.org/) files, so it's worth [familiarising yourself](https://readtapestry.com/s/un7wJiJiD/) with the language if you haven't come across it before.

## Per-prototype configuration files

The default configuration file for your prototype should be located at `/prototypes/my-prototype-name/config/common.yml` - if it doesn't exist then you will need to create it. Within that you can then add the appropriate configuration settings and overrides for your prototype.

You can also create **environment-specific configuration** files. These can take the place of the `common.yml` file, or they can be used in addition to it - so shared configuration settings can go into `common.yml` and environment-specific tweaks can go in the appropriate environment config file.

To create an environment-specific configuration file, you need to create a YAML file with a name that matches the [value of your environment key](prototypes#environments) set in your `prototypes.yml` file. So when the environment is set to `live` the Prontotype will look for a config file called `live.yml`.

## Per-extension configuration files

Prototype also allows extensions to specify their own configuration files. These behave in exactly the same way as the per-prototype config files above, but are contained within the 

## Configuration cascade

When considering your configuration strategy it's worth understanding how configuration files are loaded in Prontotype:

1. The default (system) configuration file is loaded
1. If it exists, the prototype-specific config/common.yml is loaded. Anything specified in this 


