h1 "Version: 5"
h1 "This is an H1 using double quotes looking for context var foo: #{@foo}"
h1 'This is an H1 using single quotes looking for context var foo: #{@foo}'
h2 "This is an H2 looking for local var partial: #{partial}"
partial "home"

#h3 "This is an H3 passing an argument to local function cap: #{cap('ham')}"
#ck.render(template, {context: {foo: "FOO"}, locals: {bar: "BAR", cap: cap}});
