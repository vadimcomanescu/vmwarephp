Vmwarephp
--------

Vmwarephp is a library containing bindings for vSphere API using PHP. You can
use it to manage ESX and vCenter servers. It was inspired by other efforts of
constructing vSphere bindings for other dynamically typed languages like RbvMomi
for Ruby. Currently the library supports vSphere API from version 4.1 to 5.1.1.

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
    
**Using Git / GitHub**

You can clone the latest development branch from its github repository:

    git clone git://github.com/vadimcomanescu/vmwarephp.git

Usage
--------

Vmwarephp aims to provide the least boilerplate code possibile for handling vSphere objects. The ESX/vCenter on which
the operations are performed is represented by a \Vmwarephp\Vhost object. A Vhost object provides two finder methods to 
obtain server side ManagedObjects. The following example describes a way to collect all virtual machines from a ESX/vCenter
with their config status prefetched:
    
    $vhost = new \Vmwarephp\Vhost('vcenter:443', 'Admin', 'password');
    $virtualMachines = $vhost->findAllManagedObjects('VirtualMachine', array('configStatus'));

For collecting all virtual machines with all their properties pre-fetched, which normally is a very expensive operation
considering that a lot of objects need to be traversed you can do it in the following way:

    $virtualMachines = $vhost->findAllManagedObjects('VirtualMachine', 'all');

You can find a given object using its server side id. For example finding a datastore with id datastore-182 with none of
its properties prefetched:

    $vhost = new \Vmwarephp\Vhost('vcenter:443', 'Admin', 'password');
    $datastore = $vhost->findOneManagedObject('Datastore', 'datastore-182', array());

A managed object can also be found by name:

	$vhost = new \Vmwarephp\Vhost('vcenter:443', 'Admin', 'password');
    $datastore = $vhost->findManagedObjectByName('VirtualMachine', 'myvmname', array('configStatus'));
    
Managed objects can be a generic managed object represented by \Vmwarephp\ManagedObject or a user defined extension of a
managed object defined in Extensions directory. Each managed object depending on its managed object reference type ('VirtualMachine',
'Datastore', etc.) can execute any of methods defined by the vSphere api on that reference type. For example taking a 
virtual machine snapshot can be as easy as:

    $vhost = new \Vmwarephp\Vhost('vcenter:443', 'Admin', 'password');
    $virtualMachine = $vhost->findOneManagedObject('VirtualMachine', 'vm-192', array());
    $snapshotTask = $virtualMachine->CreateSnapshot_Task(array('name' => 'snapshot_name', 'memory' => false, 'quiesce' => false));
    
or considering that the VirtualMachine managed object type has a built in extension already:

    $snapshotTask = $virtualMachine->takeSnapshot(array('name' => 'snapshot_name', 'memory' => false, 'quiesce' => false));
    
All managed object properties are defined as object accessors. You can pre-fetch all accessors when looking for the object or 
you can query an object property on the fly (note that we are not pre-fetching the configStatus property):

    $virtualMachine = $vhost->findOneManagedObject('VirtualMachine', 'vm-192', array());
    $configStatus = $virtualMachine->configStatus;
    
Or prefetching the configStatus:
    
    $virtualMachine = $vhost->findOneManagedObject('VirtualMachine', 'vm-192', array('configStatus'));
    
As you can see working with managed objects is extremely easy. Each method supported by a managed object on the server side
is mapped to a method on \Vmwarephp\ManagedObject or an extension. Properties are exposed as simple accessor methods. All data
object properties are also mapped to their respective types. A full list of defined types can be found in TypeDefinitions.inc file.

You can write your own extensions by extending the \Vmwarephp\ManagedObject and adding it to Extensions directory. 
If you consider the extensions is really useful we can easily integrate it in the main branch. Enjoy!

    
    
    
    
    
