ipupdate
========

### What is this?
A tool to send updates of IP address changes to a web server over HTTP.

### How does it work?
  * ipupdater.py
    * Runs on the client to send the updates to the web server.
    * Get its public IP address with a request to the web server (to circumvent NAT)
    * Appends an [HMAC](http://en.wikipedia.org/wiki/Hmac) to the update message being sent to the web server
  * ipupdate.php
    * Receives the updates on the web server
    * Validates the message HMAC
    * Logs the update in a SQLite database
  * getip.php
    * Show the current IP address on the web server from the last entry in the database


