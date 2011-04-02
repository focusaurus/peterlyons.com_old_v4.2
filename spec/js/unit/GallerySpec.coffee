gallery = require '../../../app/models/gallery'

describe 'the gallery model', ->

  it 'should convert YYYYMMDD dates to pretty format', ->
    gal = new gallery.Gallery("foo_20110402")
    expect(gal.displayName).toEqual "Foo April 2, 2011"

  it 'should capitalize the first letter', ->
    gal = new gallery.Gallery("baloney")
    expect(gal.displayName).toEqual "Baloney"

  it 'should set the startDate from dirName if available', ->
    gal = new gallery.Gallery("bar_20110402")
    expect(gal.startDate).toEqual(new Date(2011, 4, 2).getTime())

  it 'should not get confused by an almost-date string', ->
    gal = new gallery.Gallery("bar_201104")
    expect(gal.displayName).toEqual('Bar 201104')

  it 'should leave startDate undefined if there is no timestamp', ->
    gal = new gallery.Gallery("test_2011_airplane")
    expect(gal.startDate).toBeUndefined()
