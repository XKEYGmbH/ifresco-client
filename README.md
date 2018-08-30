ifresco Client
========================

DEPRECIATED!!! DUO ALFRESCO DROP OF THE SOAP API!

## Installation

Go into directory and execute

```
php app/console cache:clear --env=prod
php app/console cache:clear --env=dev
php app/console doctrine:schema:update --force
php app/console assets:install web --symlink
php app/console assetic:dump --env=prod
php app/console assetic:dump --env=dev
```

Make sure you set the rights afterwoods for your webserver ... e.g:

```
chown -R www-data.www-data /ifresco/dir
```

## Configuration

Create a file app/config/parameters.yml

```
parameters:
    database_driver:   pdo_mysql
    database_host:     %databaseHost%
    database_port:     ~
    database_name:     %databaseName%
    database_user:     %databaseUser%
    database_password: %****%


    mailer_transport:  smtp
    mailer_host:       127.0.0.1
    mailer_user:       ~
    mailer_password:   ~

    locale:            en
    secret:            secretKey
    alfresco_repository_url : "http://%AlfrescoSoapURL%:8080/alfresco/soapapi"
    java:              ""
    swftools:          ""
```
