ipupdate
========

### What is this?
A tool to send updates of IP address changes to a web server securely over simple HTTP.

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

#### Why is it useful?
Sometimes it's useful to connect to our home, or even host some public service there, but normally the this IP addresses are dynamically assigned and can change anytime.

#### But already exists free dynamic DNS services
This tool can be usefull as a backup to those services. since they can fail for somes reasons:
  * The service is taken down abruptlly, which [happens](http://www.noip.com/blog/2014/06/30/ips-formal-statement-microsoft-takedown/).
  * The client stops working, for example when the ISP router is switched or takes a factory reset and you forget to configure the dynamic DNS client again.

#### Why does it need to be secured?
If it doesn't use some authentication mechanism, and someone knows the procedure to update the IP address, they can change it at will, enabling attacks by directing you or someone else to other address they control, or just a simple denial of service.

#### How is it secured?
Instead of having a simple password being passed in plaintext, it uses an HMAC which produces an hash based on the message (the IP and a timestamp) and a shared password.

By having the same password configured in the ipupdater.py and ipupdate.php they generate the same hash at both ends thus verifying the authenticity of the update.
This prevents replay attacks by having a timestamp in the update message, and verifying that a new update have a more recent timestamp than the last update.

#### TODO
  * Authenticate also the response to the IP request.
  * Refactor the http code
