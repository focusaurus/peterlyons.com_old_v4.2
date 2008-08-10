import glob
from baseservlet import baseservlet
import os
import os.path
import string
import sys

#edit these values to configure for different environments
photosURI = "/photos"
photosDir = "/var/www/peterlyons.com/photos"
defaultGallery = "flagstaff_2008_part_2"

class photos(baseservlet):

    def awake(self, trans):
        baseservlet.awake(self, trans)
        self.gallery = trans.request().field("gallery", defaultGallery)
        photoName = trans.request().field("photo", None)
        self.galleries = os.listdir(photosDir)
        self.galleries.sort()
        if self.gallery not in self.galleries:
            print "WARNING: invalid gallery in request: '%s'" % self.gallery
            self.gallery = defaultGallery
        self.galleryURI = photosURI + "/" + self.gallery
        self.galleryDir = os.path.join(photosDir, self.gallery)
        #this includes full paths to both full size and thumbnail image files
        thumbnailPaths= glob.glob(os.path.join(self.galleryDir, "*" + Photo.thumbnailExtension))
        thumbnailPaths.sort()
        self.photos = []
        self.photo = None
        for path in thumbnailPaths:
            photo = Photo()
            photo.name = os.path.split(path)[1][:-7]
            photo.thumbnailURI = self.galleryURI + "/" + photo.name + Photo.thumbnailExtension
            photo.fullSizeURI = self.galleryURI + "/" + photo.name + Photo.fileExtension
            photo.caption = self.caption(photo.name)
            self.photos.append(photo)
            if photoName == photo.name:
                #this is the current photo to show full size
                self.photo = photo

        if self.photo is None:
            self.photo = self.photos[0]
            if photoName is not None:
                print "INFO: could not find photo named '%s'. Defaulting to '%s'" % (photoName, self.photo.name)
            
    def caption(self, photoName):
        altTxt = ""
        try:
            from iptcinfo import IPTCInfo
            altTxt = IPTCInfo(os.path.join(self.galleryDir, photoName + Photo.fileExtension)).data[120]
            #strip trailing unicode null byte
            if altTxt[-1] == u"\x00":
                altTxt = altTxt[:0]
            return altTxt
        except Exception, message:
            #No problem.  Fall back to .alt.txt method
            pass
        captionFile = os.path.join(self.galleryDir, photoName + Photo.captionExtension)
        if os.path.isfile(captionFile):
            try:
                altFile = file(captionFile)
                altTxt = altFile.read()
                altFile.close()
                return altTxt
            except IOError:
                print "WARNING:", sys.exc_info()[1]
        return ""
        

class Photo(object):
    fileExtension = ".jpg"
    thumbnailExtension = "-TN.jpg"
    captionExtension = ".alt.txt"

    def __init__(self):
        self.name = None
        self.fullSizeURI = None
        self.thumbnailURI = None
        self.caption = None
