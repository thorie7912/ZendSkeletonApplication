ZendMuscleApplication
=====================

Introduction
------------
This is a more enhanced, muscular version of the ZF2 skeleton application.
It demonstrates the usage of basic components such as Zend\Authentication
and Zend\Cache.

This application is meant to demonstrate the various components in ZF2 
in a way that can be seen in action from start to finish.

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

Virtual Host
------------
Afterwards, set up a virtual host to point to the public/ directory of the
project and you should be ready to go!

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

```
