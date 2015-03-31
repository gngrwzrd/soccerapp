## Soccer App ##

SoccerApp is a standalone PHP application for distributing and managing an iOS application.

## Features ##

* Manages a single app
* Manages multiple versions
* Register new devices
* Track which users have installed versions
* Install latest version or older versions
* Accept crash logs
* Delete crash logs
* Export devices for Apple Member Center
* Manually add devices

## Dashboard ##

Here's a sample dashboard https://gngrwzrd.com/soccer/MyApp/dashboard.php

## Setup ##

For each application you'd like to have soccerapp setup for. You copy the entire soccerapp folder to a new folder, then bootstrap it.

    cp soccerapp mynewapp
    cd mynewapp
    python bootstrap.py --name=MyApplication --bundlid=com.myapp.MyApp --icon=myicon.png

That's it - upload "mynewapp" to a server with PHP installed.

## Public and Private Actions ##

Registering devices and installing applications are public actions assuming the user knows the URL.

You should password protect these files and directories:

* dashboard.php
* php/

## TODO ##

-Need to get user track in  place.
