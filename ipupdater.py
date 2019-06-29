#!/usr/bin/python3
import sys, argparse, http.client, urllib.parse, time, hmac, hashlib

def get_timestamp():
    return int(time.time())

def append_hmac(msg, password):
        mac = hmac.new(password.encode(), msg.encode(), hashlib.sha256).hexdigest()
        return msg + '&h=' + mac

def validate_hmac(msg, msg_mac, password):
        new_mac = hmac.new(password.encode(), msg.encode(), hashlib.sha256).hexdigest()
        return new_mac == msg_mac

def http_post(host, url, params):
    conn = http.client.HTTPConnection(host)
    headers = {'Content-type': 'application/x-www-form-urlencoded'}
    conn.request('POST', '/' + url, params, headers)
    r = conn.getresponse()

    if r.status == 200:
        return r.read().decode('utf-8')
    else:
        print(r.status, r.reason)
        sys.exit()

def getip(args):
    timestamp = get_timestamp()
    response = http_post(args.host, args.url, 'type=ip&ts=%d' % timestamp)

    params = urllib.parse.parse_qs(response);
    ip = params['ip'][0]
    resp_timestamp = int(params['ts'][0])
    mac = params['h'][0]

    if timestamp != resp_timestamp:
        print('Error: Timestamp mismatch')
        sys.exit()

    if not validate_hmac('ip=%s&ts=%d' % (ip, resp_timestamp), mac, args.password):
        print('Error: MAC mismatch (Probably the passwords doesn\'t match)')
        sys.exit()

    return ip

def update(args, ip):
    params = append_hmac('type=update&ip=%s&name=%s&ts=%d' % (ip, args.name, get_timestamp()), args.password)
    response = http_post(args.host, args.url, params)
    print('Response: ' + response)

if __name__ == "__main__":

    parser = argparse.ArgumentParser()
    parser.add_argument('-w', '--host', help='Webserver hostname', required=True)
    parser.add_argument('-p', '--password', help='Shared password', required=True)
    parser.add_argument('-u', '--url', help='Script URL (default: ipupdate.php)', default='ipupdate.php')
    parser.add_argument('-n', '--name', help='Name to update (default: empty)', default='')
    args = parser.parse_args()

    ip = getip(args)
    update(args, ip)
