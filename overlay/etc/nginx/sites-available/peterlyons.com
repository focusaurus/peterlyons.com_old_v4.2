upstream express {
    server localhost:9400;
}

server {
    listen 80;
    #This matches peterlyons.com and *.peterlyons.com
    server_name .peterlyons.com; 
    access_log /var/log/nginx/peterlyons.com.access.log;
    error_log /var/log/nginx/peterlyons.com.error.log;

    location / {
        root /home/plyons/projects/peterlyons.com/overlay/var/www/peterlyons.com;
        index index.php index.html home.html;
        # this serves static files that exist without running other rewrite tests
        if (-f $request_filename) {
            expires 30d;
            break;
        }
        # this sends all non-existing file or directory requests to index.php
        if (!-e $request_filename) {
            rewrite ^/persblog(.+)$ /persblog/index.php?q=$1 last;
            rewrite ^/problog(.+)$ /problog/index.php?q=$1 last;
        }
    }

    # deny access to .htaccess files, if Apache's document root
    # concurs with nginx's one
    location ~ /\.ht {
        deny all;
    }

    #This is important to not break existing links
    #to /app/photos?gallery=foo&photo=bar
    rewrite /app(.*) $1;

    location /photos/ {
        proxy_pass http://express;
    }

    error_page 404 /error404.html;

    error_page 502 /error502.html;

    # pass the PHP scripts to FastCGI server
    location ~ \.php$ {
        include /etc/nginx/fastcgi_params;
        fastcgi_pass 127.0.0.1:9200;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME /home/plyons/projects/peterlyons.com/overlay/var/www/peterlyons.com$fastcgi_script_name;  # same path as above

        # required if PHP was built with --enable-force-cgi-redirect
        fastcgi_param REDIRECT_STATUS 200;
    }
}
