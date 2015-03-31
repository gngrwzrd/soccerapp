## Soccer App ##

SoccerApp is a standalone PHP application for distributing and managing an iOS application.

## Features ##

-Manages a single app
-Manages multiple versions
-Register new devices
-Track which users have installed versions
-Install latest version or older versions
-Accept crash logs
-Delete crash logs
-Export devices for Apple Member Center
-Manually add devices

## Setup ##

For each application you want to use soccerapp for, you just copy the entier soccerapp folder to a new foler, then bootstrap it.

    cp soccerapp mynewapp
    cd mynewapp
    python bootstrap --name=MyApplication --bundlid=com.myapp.MyApp --icon=myicon.png

That's it - upload "mynewapp" to a server with PHP installed.

## Public and Private Actions ##

Registering devices and installing applications are public actions assuming the user knows the URL.

You should password protect these files and directories:

-dashboard.php
-php/
-templates/

## TODO ##

-Need to get user track in  place.
