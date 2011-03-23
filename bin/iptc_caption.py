#!/usr/bin/env python
import os
import sys
from StringIO import StringIO
from iptcinfo import IPTCInfo
from optparse import OptionParser

NO_IMAGE_FILE = "Image file '%s' not found. Aborting.\n"
IMAGE_FILE_REQUIRED = "You must specify the path to your image file with -i or --image.\n"
CHANGING_CAPTION = "Replacing old caption '%s' with new caption '%s'"
ADDING_CAPTION = "Adding new caption"

def trimNull(caption):
    if caption == None:
        return None
    caption = caption.strip()
    if caption[-1] == u"\x00":
        caption = caption[:-1]
    return caption

def printCaption(options):
    #Suppress stupid warning output from IPTCInfo.py on Mac OS X
    realStdout = sys.stdout
    sys.stdout = StringIO() 
    try:
        iptcData = IPTCInfo(options.imageFile)
        sys.stdout = realStdout
        caption = iptcData.data[120]
        print trimNull(caption or "")
    except:
        #No IPTC data there. Print nothing
        pass
    finally:
        sys.stdout = realStdout

def writeCaption(options):
    oldCaption = None
    try:
        iptcData = IPTCInfo(options.imageFile, force=True)
        oldCaption = trimNull(iptcData.data[120])
        iptcData.data[120] = options.caption
        if oldCaption:
            print CHANGING_CAPTION % (oldCaption, options.caption)
        else:
            print ADDING_CAPTION
        iptcData.save()
    except Exception, message:
        sys.stderr.write(str(message) + "\n")
        sys.exit(3)

parser = OptionParser()
parser.add_option("-i", "--image", dest="imageFile",
                  help="path to image file with IPTC IIM metadata")
parser.add_option("-c", "--caption", dest="caption",
                  help="caption string for the image.  If this option is supplied, the caption will be written (or overwritten) to the image file.  If this option is omitted, the existing caption will be printed if there is one.", default=None)
(options, args) = parser.parse_args()

if options.imageFile is None:
    sys.stderr.write(IMAGE_FILE_REQUIRED)
    parser.print_help()
    sys.exit(1)
if not os.path.isfile(options.imageFile):
    sys.stderr.write(NO_IMAGE_FILE % options.imageFile)
    sys.exit(2)

if options.caption == None and len(args) > 0:
    options.caption = args[0]

if options.caption == None:
    printCaption(options)
else:
    writeCaption(options)

