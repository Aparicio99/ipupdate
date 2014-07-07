#!/usr/bin/python3
import sys, argparse, http.client, time, hmac, hashlib

def getip(args):
    conn = http.client.HTTPConnection(args.host)
    params = 'type=ip'
    headers = {'Content-type': 'application/x-www-form-urlencoded'}
    conn.request('POST', '/' + args.url, params, headers)
    r = conn.getresponse()

    if r.status == 200:
        msg = r.read().rstrip().decode('utf-8')
        print(msg)
        return msg
    else:
        print(r.status, r.reason)
        sys.exit()

def update(args, ip):

    timestamp = int(time.time())
    params = 'type=update&ip=%s&ts=%d' % (ip, timestamp)

    mac = hmac.new(args.password.encode(), params.encode(), hashlib.sha256).hexdigest()
    params += '&h=' + mac

    headers = {'Content-type': 'application/x-www-form-urlencoded'}
    conn = http.client.HTTPConnection(args.host)
    conn.request('POST', '/' + args.url, params, headers)
    r = conn.getresponse()

    if r.status == 200:
        print(r.read().rstrip().decode('utf-8'))
    else:
        print(r.status, r.reason)
        sys.exit()

if __name__ == "__main__":

    parser = argparse.ArgumentParser()
    parser.add_argument('-w', '--host', help='Webserver hostname', required=True)
    parser.add_argument('-p', '--password', help='Shared password', required=True)
    parser.add_argument('-u', '--url', help='Script URL (default: ipupdate.php)', default='ipupdate.php')
    args = parser.parse_args()

    ip = getip(args)
    update(args, ip)
