Gitki
=====

[![Build Status](https://travis-ci.org/dontdrinkandroot/gitki.php.svg?branch=master)](https://travis-ci.org/dontdrinkandroot/gitki.php)

Git backed Markdown wiki.

This software is currently in an alpha state, use it at your own risk. It is working though.

Features
--------

* Versioned, runs against a plain Git Repository
* Fully integrated (GitHub Flavored-) Markdown support
* Multi-User support
* Login via OAuth (GitHub, Google, ...)
* Easy to install
* Easy to use
* Optional Elasticsearch integration
* Responsive design
* Written in PHP
* Based on Symfony

Installation
------------

Configuration
-------------

``` yaml
# Default configuration for extension with alias: "ddr_gitki"
ddr_gitki:
    repository_path:      ~ # Required
    name:                 GitKi
    oauth:
        default_provider:     ~
        providers:
            client_id:            ~ # Required
            secret:               ~ # Required
    twig:
        show_breadcrumbs:     true
        show_toc:             true
```