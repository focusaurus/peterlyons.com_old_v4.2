from WebKit.Page import Page

class baseservlet(Page):
    """This class adds links Webware servlets to Cheetah templates.
It includes the logic to generate its HTML content by finding a Cheetah template
with the same name as this class plus the '_tmpl.tmpl' suffix.
(This template has been previously complied into a python module 'named servletname_tmpl')
It imports that template, registers itself as the template's servlet
(containing the business logic the template may need), and then renders 
the template by calling its respond method and passing the resulting
HTML string to the writeln() method."""
    
    def __init__(self):
        Page.__init__(self)
        templateName = self.__class__.__name__ + "_tmpl"     
        blankTemplateName = "blank_tmpl"
        try:
            module = __import__(templateName)
            self.templateClass = getattr(module, templateName)
        except ImportError:
            #some servelets do work and then always forward to another servlet
            #in these cases, a template file is unnecessary, so use a blank one
            module = __import__(blankTemplateName)
            self.templateClass = getattr(module, blankTemplateName)
        
    def writeHTML(self):
        #print ">> baseservlet.writeHTML()"
        templateInstance = self.templateClass()
        templateInstance.servlet = self
        self.writeln(templateInstance.respond())
        
    def hasUser(self):
        return self.session().hasValue('user')
