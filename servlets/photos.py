from baseservlet import baseservlet

class photos(baseservlet):
    pass

    def imgCell(self, imageName):
        return '''<td align="center">
<a href="#" onClick="javascript:showPhoto('%s');return true"><img border="0" src="/photos/%s-tn.jpg" /></a>
</td>''' % (imageName, imageName)
