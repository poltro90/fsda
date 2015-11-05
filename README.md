# FSDA

## Introduction

This is a sample web application that showcases a possible implementation for a system that manages user's physiological data.
The application is meant to run in a PHP web server and is compatible with the main HTML5 compatible browsers.

## Install

To install FSDA on your web server, simply copy the git repository into your web server http directory. 
Your Server should have PHP 5.5+ installed to make this work properly.
The web application will then be accessible via the web directory of your url. E.g.:

http://{HOSTNAME}/web

## Login
This web application doesn't have a connection to a database, which should be implemented separately.
Instead, there are some default login credentials to be used:

    Users:
    mario@rossi.com / password
    maria@bianchi.com / password
    
    Doctor:
    gregory@house.com / password
    
    Support:
    support@support.com / support
    
    Admin:
    admin@admin.com / admin
    
## Live Example
Live example can be found at the following link:

    http://fsda.marcobassi.com/web/


# About the code
The code is divided in frontend ('web' directory), and backend ('rest' directory). User should access the web application only from the frontend side. Although, there are no limitation on extending this so to be used for broader usage outside the web app.

## Frontend
Based on AdminLTE template (check 'web/LICENSE'), it is build with some of the most used web technologies and frameword, such as:
    
    Bootstrap
    jQuery
    Chart.js
    Flot.js
    Routie
    
Thanks to the various tools provided by Bootstrap, the application is versatile and easy to mantain and customize, as well as being modular and dynamic.
Additional work is required in order to implement all the remaining functions, but the overall picture and stucture is well defined in the given example.

## Backend
The server side of the application is built in PHP and uses the Slim framework ('rest/Slim/') to define the various REST API routes.
Response is in JSON format for multi compatibility.

The current source of the data is currently taken from .csv files but, for live performances, a SQL/NoSQL database is required.

Rest APIs can be accesses via

    https://{HOSTNAME}/rest/{API}
