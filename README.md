## What is it ?
SolusVM-Status is a quick way to check up on your SolusVM VPS to see if it is online, check out the memory / disk space / bandwith usage and quickly shutdown, reboot or boot the VPS without entering the SolusVM control panel.

The script runs using PHP and Curl, interfacing with the [SolusVM Client API](http://docs.solusvm.com/client_api) which can be easily activated through the SolusVM control panel. It's pretty basic, only requiring one simple PHP file and 3 lines of configuration before usage (API key, API hash and the SolusVM master server address). The amazing UI is courtesy of vanilla [Bootstrap](http://getbootstrap.com).

## What's it look like ?
This.

![SolusVM Screenshot](http://i.imgur.com/KXhp30h.png)

## Why such poor coding ?
Because I just wanted something functional, fast. And in the end, it works, so I'm happy.

## Anything else ?
Feel free to modify, update it and whatever else, just make sure you secure access to the PHP script, otherwise anyone can find the file and click shutdown repeatedly just for "teh lulz". Have fun now.
