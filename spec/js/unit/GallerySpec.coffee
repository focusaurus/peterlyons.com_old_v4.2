gallery = require '../../../app/models/gallery'

describe 'the gallery model', ->
  describe 'constructor', ->
    it 'should not mess with dirName', ->
      gal = new gallery.Gallery("foo_20110402")
      expect(gal.dirName).toEqual "foo_20110402"

    describe 'displayName default computation based on dirName', ->
      it 'should capitalize the first letter', ->
        gal = new gallery.Gallery("baloney_sandwich")
        expect(gal.displayName.slice(0, 7)).toEqual "Baloney"

      it 'should replace underscores with spaces', ->
        gal = new gallery.Gallery("baloney_sandwich_bingo-time")
        expect(gal.displayName).toEqual "Baloney sandwich bingo-time"

      it 'should convert YYYYMMDD dates in to pretty format', ->
        gal = new gallery.Gallery("foo_20110402")
        expect(gal.displayName).toEqual "Foo April 2, 2011"

      it 'should not get confused by an almost-date string', ->
        gal = new gallery.Gallery("bar_201104")
        expect(gal.displayName).toEqual('Bar 201104')

    describe 'startDate default computation based on dirName', ->
      it 'should set the startDate from dirName if available', ->
        gal = new gallery.Gallery("bar_20110402")
        expect(gal.startDate).toEqual(new Date(2011, 3, 2))

      it 'should set the startDate from dirName if available with displayName set', ->
        gal = new gallery.Gallery("bar_20110402", "Bar Far")
        expect(gal.startDate).toEqual(new Date(2011, 3, 2))

      it 'should not get confused by an almost-date string', ->
        gal = new gallery.Gallery("bar_201104")
        expect(gal.startDate).toBeNull()

      it 'should leave startDate undefined if there is no timestamp', ->
        gal = new gallery.Gallery("test_2011_airplane")
        expect(gal.startDate).toBeNull()

    describe 'startDate parsing', ->
      it 'should handle YYYYMMDD', ->
        gal = new gallery.Gallery("test", "test", "20110402")
        expect(gal.startDate).toEqual(new Date(2011, 3, 2))

      it 'should handle YYYYMMDD as object', ->
        gal = new gallery.Gallery("test", "test", new String("20110402"))
        expect(gal.startDate).toEqual(new Date(2011, 3, 2))

      it 'should handle MM/DD/YYYY', ->
        gal = new gallery.Gallery("test", "test", "04/02/2011")
        expect(gal.startDate).toEqual(new Date(2011, 3, 2))

      it 'should handle MM/DD/YY', ->
        gal = new gallery.Gallery("test", "test", "04/02/11")
        expect(gal.startDate).toEqual(new Date(2011, 3, 2))

      it 'should handle years before 2000', ->
        gal = new gallery.Gallery("test", "test", "08/01/1999")
        expect(gal.startDate).toEqual(new Date(1999, 7, 1))

      it 'should handle ISO 8601 format', ->
        gal = new gallery.Gallery("test", "test",
          new Date(1999, 7, 1).toISOString())
        expect(gal.startDate).toEqual(new Date(1999, 7, 1))

    describe 'URI', ->
      it 'should return the right value', ->
        gal = new gallery.Gallery("dirName1", "Test Gal", "20110402")
        expect(gal.URI()).toEqual('/app/photos?gallery=dirName1')
