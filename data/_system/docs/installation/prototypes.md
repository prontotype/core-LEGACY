# Prototypes and domains

By default, Prontotype is configured tp serve up a single prototype at whichever domain you like. However it is also possible to configure a single Prontotype install to serve up multiple prototypes for different domains, should you choose.

## Location and file structure of a prototype

All prototypes live in the `/prototypes` directory, and at the bare minumum consist of a folder containing a `/templates` subfolder, within which is a file named `index.twig`. The name of the parent folder is effectively the 'short name' of your prototype.

<pre>
<code>
    - prototypes
        - example
            - templates
                - index.twig
</code>    
</pre>

## The prototypes.yml file

