ZendSkeletonApplication++ (An Enhanced Skeleton)
================================================

Introduction
------------
This skeleton-ish application demonstrates various components of ZF2 in action. It
has examples for fairly commonly needed capabilities such as:

This application is meant to demonstrate the various components in ZF2
in a way that can be seen in action from start to finish.

* Authentication via Form
* Login Forms (Rendering, Submitting, Validating, Processing)
* Session Handling with Custom Cached Storage Options (uses memcached)
* Form Error Handling

If you'd like to add another common capability, please submit pull requests.

Desired (need contributors):

* Demonstrate Zend\Navigation
* Demonstrate more Zend\Db capabilities
* Demonstrate Zend\Permissions\Acl
* Demonstrate Zend\Cache (APC?)
* Demonstrate REST controllers, routing, header-based versions and response type (accepts)
* ... other suggestions? what would you like to see demonstrated?

Installation
------------

Using Composer (recommended)
----------------------------
The recommended way to get a working copy of this project is to clone the repository
and use composer to install dependencies:

    cd my/project/dir
    git clone git://github.com/thorie7912/ZendSkeletonApplication.git
    cd ZendSkeletonApplication
    php composer.phar install

Using Git submodules
--------------------
Alternatively, you can install using native git submodules:

    git clone git://github.com/thorie7912/ZendSkeletonApplication.git --recursive

Setup and Run Memcached
-----------------------

Something like... (depends on your system)

    apt-get install memcached
    /etc/init.d/memcached start

Also, be sure you have the memcached PHP extension

    php -i | grep memcached


Prepare Your Database
---------------------

Here are some mysql commands you can use to get you started

```sql
CREATE DATABASE `skeleton`;

GRANT ALL ON `skeleton`.* TO 'myuser'@'localhost' IDENTIFIED BY 'mypass';

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `firstName` varchar(128) NOT NULL,
  `lastName` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `status` varchar(128) DEFAULT '' NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

insert into users (email,firstName,lastName,password) values('test@test.com', 'John', 'Doe', PASSWORD('test'));

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(60) NOT NULL,
  `data` longtext NOT NULL,
  `lifetime` int(10) unsigned,
  `modified` int(10) unsigned,
  `name` varchar(60) DEFAULT 'PHPSESSID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


```
Virtual Host
------------
Afterwards, set up a virtual host to point to the public/ directory of the
project and you should be ready to go!
