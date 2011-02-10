app = require('express').createServer()

app.register '.coffee', require('coffeekup')
app.set 'view engine', 'coffee'

app.get '/', (req, res) ->
  res.render 'home'

app.listen 3080

