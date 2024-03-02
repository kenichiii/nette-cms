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

DEVELOPMENT
-------

Phase 1
------
- Admin
  - forms
    - with loader
    - better error handling for ajax
    - form-body render
  - language detector
- App
  - add cache for pages, settings
  - pages list without content -> pageService->getPageContent(id)
- ORM
  - allow uppercase in column name
  - repository alter table
  - relations
  - model(group) __call -> getters, setters
  - automatic forms creation
  - validation function

Phase 2 
-------
 - Admin
   - add javascript translation service

   - pages
       - tree new page better background for input
       - files, gallery, photo
   - users
     - better roles handling
   
 - Front
   - user -> sign(fb,google), profile
   - breadcrumbs

 - App
   - translations
       - front from file
       - admin from file

Phase 3
-------
- documentation
- gallery, files
- user
  - profile settings (sounds, notification and messages to email)
  - to-do list
  - notifications
  - messages
- development
  - pfc editor
  - adminer.php
  - create, alter table
  - create model, repository, service, front presenter
  - image editor -> https://github.com/AyushmanSarkar/PocketPainter?tab=readme-ov-file
