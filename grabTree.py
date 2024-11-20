# pip install getfilelistpy

from google.oauth2 import service_account
from getfilelistpy import getfilelist
import json
import sys
import argparse

SCOPES = ['https://www.googleapis.com/auth/drive']
parser = argparse.ArgumentParser()
parser.add_argument("-fid","--folder-id", help="Google drive folder ID")
parser.add_argument("-kf","--key-file", help="Google drive credentials file path")
parser.add_argument("-d","--delegate", help="The email of the user to impersonate.")
args = parser.parse_args()
if args.folder_id == None:
    print('Missing --folder-id argument')
    sys.exit()

if args.key_file == None:
    print('Missing --key-file argument')
    sys.exit()


credentials = service_account.Credentials.from_service_account_file(args.key_file, scopes=SCOPES)
if args.delegate != None:
    credentials = credentials.with_subject(args.delegate)


resource = {
    "service_account": credentials,
    "id": args.folder_id,
    "fields": "files(name,id, size)",
}
res = getfilelist.GetFileList(resource)  # or
#res = getfilelist.GetFolderTree(resource)
json = json.dumps(res, indent=4)
print(json)