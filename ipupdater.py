#!/usr/bin/python3
import sys, http.client, time, hmac, hashlib

HOST      = '<your host>'
URL       = '/ipupdate.php'
PASSWORD  = 'some random password'
HASH_ALGO = hashlib.sha256

def getip():
    conn = http.client.HTTPConnection(HOST)
    params = 'type=ip'
    headers = {'Content-type': 'application/x-www-form-urlencoded'}
    conn.request('POST', URL, params, headers)
    r = conn.getresponse()

    if r.status == 200:
        return r.read().rstrip().decode('utf-8')
    else:
        print(r.status, r.reason)
        sys.exit()

def update(ip):

    timestamp = int(time.time())
    params = 'type=update&ip=%s&ts=%d' % (ip, timestamp)

    mac = hmac.new(PASSWORD.encode(), params.encode(), HASH_ALGO).hexdigest()
    params += '&h=' + mac

    headers = {'Content-type': 'application/x-www-form-urlencoded'}
    conn = http.client.HTTPConnection(HOST)
    conn.request('POST', URL, params, headers)
    r = conn.getresponse()

    if r.status == 200:
        print(r.read().rstrip().decode('utf-8'))
    else:
        print(r.status, r.reason)
        sys.exit()

ip = getip()
update(ip)
