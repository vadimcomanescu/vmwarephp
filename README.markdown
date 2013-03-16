Vmwarephp
--------

Vmwarephp is a library containing bindings for vSphere API using PHP. You can
use it to manage ESX and VCenter servers. It was inspired by other efforts of
constructing Vsphere bindings for other dynamically typed languages like RbvMomi
for Ruby. Currently the library supports vSphere API from version 4.1 to 5.0.1.

Prerequisites
--------

Vmwarephp requires PHP version 5.3.2 or greater with soap and ssl enabled. For more
information on how to compile PHP from source and enable extensions please check: http://www.php.net/manual/en/install.php.

Installation
--------

Vmwarephp can be installed using Composer or by cloning it from its GitHub repository.

**Using Composer**

You can read more about Composer and its main repository at
[http://packagist.org](http://packagist.org "Packagist"). To install
Vmwarephp using Composer, first install Composer following the instructions on the
Packagist home page. After that, you can define a dependency on Vmwarephp by adding
the following lines to your composer.json:

    {
        "require": {
            "vmwarephp/vmwarephp": "dev-master"
        }
    }