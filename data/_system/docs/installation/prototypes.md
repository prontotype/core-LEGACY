# Prototypes and domains

By default, Prontotype is configured to serve up a single prototype at whichever domain you like. However it is also possible to configure a single Prontotype install to serve up multiple prototypes for different domains, should you choose. 

## Location and file structure of a prototype

All prototypes live in the `/prototypes` directory, and at the bare minumum consist of a **parent folder** (called whatever you like - this is your prototype's 'name') containing a `/templates` subfolder, within which is a file named `index.twig`.

So the most basic prototypes directory might look like this: 

![Minimal prototypes directory structure](/index.php/_assets/_system/img/docs/prototype-minimal-structure.png)

Here there is one prototype (called **default**) with just one page in it, which is the homepage file `index.twig` (in the later [Pages and URLs](#) section you'll learn how requests are routed to templates).

Each prototype directory can also contain subdirectories with configuration settings, data files, assets and extensions - all of which are discussed in later sections.

## The prototypes.yml file

At the root level of the Prontotype directory structure there is a `prototypes.yml` file, which handles deciding which domains match up to which prototype. The default contents of this file are as follows:

<pre><code>default:
  matches: '*'
  prototype: default
  environment: live
</code></pre>

This configuration will result in Prontotype will trying and serve up the default prototype (i.e. the one in a folder named 'default') for *any* domain. It will also set the environment value to 'live' (see the [configuration section](#) for more detail on environments).

If you wanted to serve up an additional prototype (one in a folder named **foo** within the `/prototypes` directory) at a different domain, you could amend the `prototypes.yml` file to look like this:

<pre><code>foo_prototype:
  matches: 'foo.com'
  prototype: foo
  environment: live

default:
  matches: '*'
  prototype: default
  environment: live
</code></pre>

This would serve up the **foo** prototype to all **foo.com** URLs, and the default prototype for all other URLs. Note that the reference name (e.g. 'foo_prototype') can be anything you like - it has no bearing on which prototype is served up.

The value of the `prototype` key should be the name of the prototype folder that you wish to serve up for that domain.

<p class="note">Note that the more specific URL patterns should be specified first in the file - Prontotype will stop looking as soon as it finds a domain to match.</p>

### URL matching patterns

The value of the `matches` key uses a simple wildcard pattern to match URLs against. Below are some examples of patterns and the resulting URLs that would be matched:

| Pattern | URL matches |
| ----- | ----- |
| foo.com | foo.com |
| foo.* | foo.com, foo.dev, foo.site etc |
| *.dev | foo.dev, bar.dev, test.dev etc |
| *.foo.com | bar.foo.com, hello.foo.com etc |
| \*.foo.\* | bar.foo.com, bar.foo.site, hello.foo.com, etc |


