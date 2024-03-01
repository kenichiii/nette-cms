# nette-cms
CMS in Nette framework

not completed yet

Features
---------

- supports multiple languages

- seo friendly

- support multiple layouts

- user management

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
- forms 
  - with loader
  - better error handling for ajax
- better photo upload -> crop, naja
- language detector
- clear cache button
- add pfc alerts
- datagrid 
  - active sorting
  - paging styles
- pages
    - tree new page better background for input
    - files, gallery, photo
- user
  - profile settings
  - to-do list
  - notifications
  - messages
- users
  - new user form -> clear, mail
  - edit user form
  - view user details
  - better roles handling
- settings
- gallery, files
- development
  - pfc editor
  - adminer.php
  - create, alter table
  - create model, repository, service, front presenter
  - image editor -> https://github.com/AyushmanSarkar/PocketPainter?tab=readme-ov-file
Front
-----
- user -> sign(fb,google), profile
- breadcrumbs

App
---
- cache
- translations
    - front from database
    - admin from file
- pages list without content -> pageService->getPageContent(id)

ORM
---
- allow uppercase in column name
- repository alter table
- relations
- model(group) __call -> getters, setters
- automatic forms creation
- validation function