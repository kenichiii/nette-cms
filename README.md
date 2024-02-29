# nette-cms
CMS in Nette framework

not completed yet

Features
---------

- supports multiple languages

- seo friendly

- support multiple layouts

- users management

- easy install

- run also from sub folder

- responsible Admin template using bootstrap 4

- use dibi for database connection

- own reuseable ORM 


Requirements
------------

- requires PHP 8.1 and MYSQL database


Installation
------------
The best way to install Web Project is using Composer. If you don't have Composer yet,
download it following [the instructions](https://doc.nette.org/composer). Then use command:

	composer update

Make directories `temp/` and `log/` writable.

configure `config/local.neon`

open link `/admin123/install.default/` and install database you will be automaticly logged in as admin due to setup in local.neon 

after install delete `app/AdminModule/InstallModule`

MISSING:
-------

Admin
-----
- datagrid -> make responsible(buttons)
- add pfc alerts
- pages
    - tree new page better background for input
    - files, gallery, photo
    - add loading
- account settings
- change password
- to-do list
- users
  - new user form
  - edit user form
  - view user details
- settings
- gallery, files
- notifications
- messages

Front
-----
- user -> sign(fb,google), profile

App
---
- translations
    - front from database
    - admin from file

ORM
---
- repository alter table
- relations
- model(group) __call -> getters, setters
- automatic forms creation