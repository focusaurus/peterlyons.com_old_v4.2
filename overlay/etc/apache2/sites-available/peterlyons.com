<VirtualHost *:80>
    ServerName www.peterlyons.com
    ServerAlias peterlyons.com
    ServerAdmin webmaster@peterlyons.com

    DocumentRoot /var/www/peterlyons.com
    <Directory />
        AllowOverride FileInfo
	Options FollowSymLinks
    </Directory>
    <Location /misc/>
        Options Indexes
    </Location>
    <Location /app>
        WKServer localhost 9000
        SetHandler webkit-handler
    </Location>
    CustomLog /var/log/apache2/www.peterlyons.com.access.log combined
    Errorlog /var/log/apache2/www.peterlyons.com.error.log

    # Possible values include: debug, info, notice, warn, error, crit,
    # alert, emerg.
    LogLevel warn
</VirtualHost>
