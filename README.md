ipupdate
========

### What is this?
A tool to send updates of IP address changes to a web server securely over simple HTTP.

### How does it work?
  * ipupdater.py
    * Runs on the client to send the updates to the web server.
    * Get its public IP address with an authenticated message from the web server (to circumvent NAT).
    * Send an authenticated update message to the web server.
  * ipupdate.php
    * Receives the updates on the web server.
    * Validates the message authentication.
    * Logs the update in a **SQLite** database.
  * getip.php
    * Show the current IP address on the web server from the last entry in the database.

### How can I get it to work?

**Requirements**
  * A webserver with any version of PHP (just needs SQLite enabled).
  * Python 3.x in the client machine.

**Steps**
  * Put ipupdate.php and getip.php on a web server with PHP.
  * Put ipupdater.py anywhere in the machine you want to serve as client.
  * Set the PASSWORD variable in ipupdate.php-
  * Change the names of the files if you don't want the original names.
  * Use a cronjob in your client to run the ipupdater.py at the interval you want, like 1 hour.
  * Access getip.php on the web server to see the current IP address

The usage of ipupdater.py is:

```ipupdater.py -w HOST -p PASSWORD [-u URL]```

### Why is it useful?
Sometimes it's useful to connect to our home, or even host some public service there, but normally this IP addresses are dynamically assigned and can change anytime.

Can also be used to get the history log of the IP addresses changes.

### But if already exists free dynamic DNS services?
This tool can be usefull as a backup to those services. since they can fail for somes reasons:
  * The service is taken down abruptlly, which [happens](http://www.noip.com/blog/2014/06/30/ips-formal-statement-microsoft-takedown/).
  * The client stops working, for example when the ISP router is switched or takes a factory reset and you forget to configure the dynamic DNS client again.

Or you just don't like the security of those services protocols.

### Why does it need to be secured?
If it doesn't use some authentication mechanism, and someone knows the procedure to update the IP address, they can change it at will, enabling attacks by directing you or someone else to other address they control, or just a simple denial of service.

### How is it secured?
Instead of having a simple password being passed in plaintext, it uses an [HMAC](http://en.wikipedia.org/wiki/Hmac) which produces an hash based on the message (the IP and a timestamp) and a shared password.

By having the same password configured in the **ipupdater.py** and **ipupdate.php** they generate the same hash at both ends thus verifying the authenticity of the update.
This prevents replay attacks by having a timestamp in the  messages, and verifying that a new update have a more recent timestamp than the last update.
