# queueManage
Complete queue management system. It can handle tickets, desks, operators, room displays, totem, etc.

There are administration pages where you can edit some basic features.

It also has a nice API to use with mobile phones!

It still laks an attractive skin, but it's completely functional.

At the moment GUI is only in italian. Internationalization will be added in the future.

## How to install
* Override settings defined in `/includes/DefaultSettings.php` creating new file `LocalSettings.php` in root directory
* Configure Apache server with rewrite rules as specified in `/ApacheRewrite`
* Create database schema using `/includes/entities/sql/fastqueue.sql`
* Optionally add some fixtures using `/includes/entities/sql/fixtureTicketStats.sql`
* Ready to go!
