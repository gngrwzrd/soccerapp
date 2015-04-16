import argparse,re,os,shutil,urllib,subprocess,json
parser = argparse.ArgumentParser(description="Bootstrap a soccer instance.")
parser.add_argument("-e","--enterprise",action="store_true",help="The application is signed with enterprise certificates and provisions.")
parser.add_argument("-s","--sign",action="store_true",help="(Optional) sign the mobileconfig with your servers ssl certificates.")
parser.add_argument("--ios",action="store_true",help="This is an ios app.")
parser.add_argument("--mac",action="store_true",help="This is a mac app.")
parser.add_argument("--android",action="store_true",help="This is an android app.")
parser.add_argument("--name",help="The application name. ex: Sweet Camera, Awesome Grub, etc.")
parser.add_argument("--bundleid",help="The application's bundle id. ex: com.example.MyExampleApp.")
parser.add_argument("--icon",help="The application icon")
parser.add_argument("--sslcrt",help="Your servers crt file.")
parser.add_argument("--sslkey",help="Your servers key file. (Passwords not supported).")
parser.add_argument("--sslchain",help="Your servers cert chain file.")
args = parser.parse_args()

def writeFile(path,content):
	handle = open(path,"w")
	handle.write(content)
	handle.close()

def bootstrap(args):
	data = {
		'enterprise':args.enterprise,
		'name':args.name,
		'bundleId':args.bundleid,
	}
	if args.mac: data['type'] = 'mac'
	if args.ios: data['type'] = 'ios'
	if args.android: data['type'] = 'android'
	content = json.dumps(data)
	writeFile("app.json",content)

	if args.icon:
		shutil.copy("assets/icon.png","assets/default-icon.png")
		shutil.copy(args.icon,"assets/icon.png")

bootstrap(args)
